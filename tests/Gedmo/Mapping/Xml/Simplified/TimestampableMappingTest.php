<?php

declare(strict_types=1);

/*
 * This file is part of the Doctrine Behavioral Extensions package.
 * (c) Gediminas Morkevicius <gediminas.morkevicius@gmail.com> http://www.gediminasm.org
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gedmo\Tests\Mapping\Xml\Simplified;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Gedmo\Tests\Mapping\Fixture\Xml\Status;
use Gedmo\Tests\Mapping\Fixture\Xml\Timestampable;
use Gedmo\Tests\Tool\BaseTestCaseORM;
use Gedmo\Timestampable\TimestampableListener;

/**
 * These are mapping extension tests
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 */
final class TimestampableMappingTest extends BaseTestCaseORM
{
    /**
     * @var TimestampableListener
     */
    private $timestampable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timestampable = new TimestampableListener();
        $evm = new EventManager();
        $evm->addEventSubscriber($this->timestampable);

        $this->getMockSqliteEntityManager($evm);
    }

    public function testTimestampableMetadata()
    {
        $meta = $this->em->getClassMetadata(Timestampable::class);
        $config = $this->timestampable->getConfiguration($this->em, $meta->getName());

        static::assertArrayHasKey('create', $config);
        static::assertSame('created', $config['create'][0]);
        static::assertArrayHasKey('update', $config);
        static::assertSame('updated', $config['update'][0]);
        static::assertArrayHasKey('change', $config);
        $onChange = $config['change'][0];

        static::assertSame('published', $onChange['field']);
        static::assertSame('status.title', $onChange['trackedField']);
        static::assertSame('Published', $onChange['value']);
    }

    protected function getMetadataDriverImplementation()
    {
        $xmlDriver = new SimplifiedXmlDriver([
            __DIR__.'/../../Driver/Xml' => 'Gedmo\Tests\Mapping\Fixture\Xml',
        ]);

        $chain = new MappingDriverChain();
        $chain->addDriver($xmlDriver, 'Gedmo\Tests\Mapping\Fixture\Xml');

        return $chain;
    }

    protected function getUsedEntityFixtures()
    {
        return [
            Timestampable::class,
            Status::class,
        ];
    }
}
