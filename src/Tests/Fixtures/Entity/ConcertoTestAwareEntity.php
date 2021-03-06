<?php

namespace Ctrl\Bundle\ConcertoBundle\Tests\Fixtures\Entity;


use Ctrl\Bundle\ConcertoBundle\Model\SoloistAwareInterface;
use Ctrl\Bundle\ConcertoBundle\Model\Soloist;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="concerto_aware")
 */
class ConcertoTestAwareEntity implements SoloistAwareInterface
{

    /**
     * @var integer
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    public $pubprop = 5;

    /**
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer")
     */
    protected $clientId;

    /**
     * @var \Ctrl\Bundle\ConcertoBundle\Model\Soloist
     *
     * @ORM\ManyToOne(targetEntity="Ctrl\Bundle\ConcertoBundle\Tests\Fixtures\Entity\ConcertoTestSoloist")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $soloist;

    public function getSoloist()
    {
        return $this->soloist;
    }

    public function setSoloist(Soloist $soloist = null)
    {
        $this->soloist = $soloist;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
