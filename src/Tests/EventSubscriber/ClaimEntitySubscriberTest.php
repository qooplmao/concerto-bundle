<?php

namespace Ctrl\Bundle\ConcertoBundle\Tests\EventSubscriber;


use Ctrl\Bundle\ConcertoBundle\EventSubscriber\ClaimEntitySubscriber;
use Ctrl\Bundle\ConcertoBundle\Tests\ConcertoTestCase;

class ClaimEntitySubscriberTest extends ConcertoTestCase
{

    function getMockEntity()
    {
        return $this->getMockBuilder('Ctrl\Bundle\ConcertoBundle\Tests\Fixtures\Entity\ConcertoConcertoFakeEntity')
            ->setMethods(['setSoloist', 'getSoloist'])
            ->enableProxyingToOriginalMethods()
            ->getMock();
    }

    function testItsOnFlushMethodWorks()
    {
        $soloistMock = $this->mock('Ctrl\Bundle\ConcertoBundle\Model\Soloist')
            ->getId(1)
            ->new();
        
        $entities = ['i' => [ $this->getMockEntity(), $this->getMockEntity(), $this->getMockEntity() ],
                     'u' => [ $this->getMockEntity(), $this->getMockEntity(), $this->getMockEntity() ]
                     #, 'd' => [ $this->getMockEntity(), $this->getMockEntity(), $this->getMockEntity() ]
        ];

        foreach($entities as $k => $v)
        {
            if($k == 'i' || $k == 'u') {

                foreach($v as $entity)
                {
                    $entity->expects($this->once())->method('setSoloist')->with($soloistMock);
                }
            } #elseif no case for deletes because they're gonna be deleted anyway.
        }

        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->setMethods(['getScheduledEntityInsertions', 'getScheduledEntityUpdates', 'getScheduledEntityDeletions'])
            ->getMock();

        $uow->expects($this->once())->method('getScheduledEntityInsertions')->willReturn($entities['i']);
        $uow->expects($this->once())->method('getScheduledEntityUpdates')->willReturn($entities['u']);
        #$uow->expects($this->once())->method('getScheduledEntityDeletions')->willReturn($entities['d']);

        $em = $this->getMockBuilder('Ctrl\Bundle\ConcertoBundle\ORM\Conductor')
            ->disableOriginalConstructor()
            ->setMethods(['getSoloist', 'getUnitOfWork'])
            ->getMock();
        $em->expects($this->once())->method('getSoloist')->willReturn($soloistMock);
        $em->expects($this->once())->method('getUnitOfWork')->willReturn($uow);

        $OFEA = $this->getMockBuilder('Doctrine\ORM\Event\OnFlushEventArgs')
            ->disableOriginalConstructor()
            ->setMethods(['getEntityManager'])
            ->getMock();
        $OFEA->expects($this->once())->method('getEntityManager')->willReturn($em);

        $sut = new ClaimEntitySubscriber($this->mock('Symfony\Component\DependencyInjection\ContainerInterface', null));
        $sut->onFlush($OFEA);

        foreach($entities as $k => $v)
        {
            if($k == 'i' || $k == 'u') {

                foreach($v as $entity)
                {
                    $this->assertSame($soloistMock, $entity->getSoloist());
                }
            } else {

                foreach($v as $entity)
                {
                    $this->assertNull($entity->getSoloist());
                }
            }
        }
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage OnFlush: Soloist should be set by now.
     */
    function testOnFlushErrorsWithNoSoloist()
    {
        $em = $this->mock('Ctrl\Bundle\ConcertoBundle\ORM\Conductor')
            ->getSoloist(null)
            ->getUnitOfWork(null)
            ->new();

        $OFEA = $this->mock('Doctrine\ORM\Event\OnFlushEventArgs')
            ->getEntityManager($em)->new();

        $sut = new ClaimEntitySubscriber($this->mock('Symfony\Component\DependencyInjection\ContainerInterface', null));
        $sut->onFlush($OFEA);
    }
}
