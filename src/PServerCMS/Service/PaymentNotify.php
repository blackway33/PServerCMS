<?php

namespace PServerCMS\Service;

use PaymentAPI\Provider\Request;
use PaymentAPI\Service\LogInterface;
use PServerCMS\Entity\DonateLog;
use PServerCMS\Entity\User;

class PaymentNotify extends InvokableBase implements LogInterface
{

    /**
     * Method the add the reward
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function success( Request $request )
    {

        $user = $this->getUser4Id( $request->getUserId() );
        if (!$user) {
            throw new \Exception( 'User not found' );
        }

        // we already added add the reward, so skip this =)
        if ($this->isDonateAlreadyAdded( $request )) {
            return true;
        }

        // check if donate should add coins or remove
        $coins = $request->getStatus() == $request::STATUS_SUCCESS ? abs( $request->getAmount() ) : -$request->getAmount();

        // save the message if gamebackend-service is unavailable
        $errorMessage = '';
        try {
            $this->getCoinService()->addCoins( $user, $coins );
        } catch( \Exception $e ) {
            $request->setStatus( $request::STATUS_ERROR );
            $errorMessage = $e->getMessage();
        }

        if ($request->getStatus() == $request::STATUS_CHARGE_BACK) {
            $expire = (int)$this->getConfigService()->get( 'payment-api.ban-time', 0 ) + time();
            $reason = 'Donate - ChargeBack';

            $this->getUserBlockService()->blockUser( $user, $expire, $reason );
        }

        $this->saveDonateLog( $request, $user, $errorMessage );

        return true;
    }

    /**
     * Method to log the error
     *
     * @param Request    $request
     * @param \Exception $e
     *
     * @return bool
     */
    public function error( Request $request, \Exception $e )
    {
        $user = $this->getUser4Id( $request->getUserId() );

        $this->saveDonateLog( $request, $user, $e->getMessage() );
    }

    /**
     * @param Request $request
     * @param User   $user
     *
     * @return DonateLog
     */
    protected function getDonateLogEntity4Data( Request $request, $user, $errorMessage = '' )
    {

        $data = $request->toArray();
        if ($errorMessage) {
            $data['errorMessage'] = $errorMessage;
        }

        $donateEntity = new DonateLog();
        $donateEntity->setTransactionId( $request->getTransactionId() )
            ->setCoins( $request->getAmount() )
            ->setIp( $request->getIp() )
            ->setSuccess( $request->getStatus() )
            ->setType( $this->mapPaymentProvider2DonateType( $request ) )
            ->setDesc( json_encode( $data ) );

        if ($user) {
            $donateEntity->setUser( $user );
        }

        return $donateEntity;
    }

    /**
     * @param Request $request
     * @param User   $user
     */
    protected function saveDonateLog( Request $request, $user, $errorMessage = '' )
    {
        $donateLog     = $this->getDonateLogEntity4Data( $request, $user, $errorMessage );
        $entityManager = $this->getEntityManager();
        $entityManager->persist( $donateLog );
        $entityManager->flush();
    }

    /**
     * Helper to map the PaymentProvider 2 DonateType
     *
     * @param Request $request
     *
     * @return string
     */
    protected function mapPaymentProvider2DonateType( Request $request )
    {
        $result = '';
        switch ($request->getProvider()) {
            case Request::PROVIDER_PAYMENT_WALL:
                $result = DonateLog::TYPE_PAYMENT_WALL;
                break;
            case Request::PROVIDER_SUPER_REWARD:
                $result = DonateLog::TYPE_SUPER_REWARD;
                break;
        }
        return $result;
    }

    /**
     * check is donate already added, if the provider ask, more than 1 time, this only works with a transactionId
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isDonateAlreadyAdded( Request $request )
    {
        /** @var \PServerCMS\Entity\Repository\DonateLog $donateEntity */
        $donateEntity = $this->getEntityManager()->getRepository( $this->getEntityOptions()->getDonateLog() );
        return $donateEntity->isDonateAlreadyAdded( $request->getTransactionId(), $this->mapPaymentProvider2DonateType( $request ) );
    }

    /**
     * @return Coin
     */
    protected function getCoinService()
    {
        return $this->getService('pserver_coin_service');
    }
}