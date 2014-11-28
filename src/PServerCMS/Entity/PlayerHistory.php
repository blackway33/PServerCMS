<?php
/**
 * Created by PhpStorm.
 * User: †KôKšPfLâÑzè®
 * Date: 25.11.2014
 * Time: 00:36
 */

namespace PServerCMS\Entity;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use PServerCMS\Keys\Caching;
use PServerCMS\Service\ServiceManager;

/**
 * PlayerHistory
 *
 * @ORM\Table(name="playerHistory")
 * @ORM\Entity(repositoryClass="PServerCMS\Entity\Repository\PlayerHistory")
 * @ORM\HasLifecycleCallbacks
 */
class PlayerHistory {

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	private $id;

	/**
	 * @var int
	 *
	 * @ORM\Column(name="player", type="smallint", nullable=false)
	 */
	private $player;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created", type="datetime", nullable=false)
	 */
	private $created;

	public function __construct( ) {
		$this->created = new \DateTime();
	}

	/**
	 * @ORM\PostPersist()
	 */
	public function postPersist( LifecycleEventArgs $eventArgs ) {
		/** @var PageInfo $entity */
		$entity = $eventArgs->getEntity();

		/** @var \PServerCMS\Service\CachingHelper $cachingHelperService */
		$cachingHelperService = ServiceManager::getInstance()->get('pserver_cachinghelper_service');
		$cachingHelperService->delItem(Caching::PlayerHistory);
		//$em->getUnitOfWork()->getS
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getPlayer() {
		return $this->player;
	}

	/**
	 * @param int $player
	 *
	 * @return PlayerHistory
	 */
	public function setPlayer( $player ) {
		$this->player = $player;

		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @param \DateTime $created
	 *
	 * @return PlayerHistory
	 */
	public function setCreated( $created ) {
		$this->created = $created;

		return $this;
	}
} 