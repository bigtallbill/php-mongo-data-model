<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 24/12/14
 * Time: 14:06
 */

namespace Bigtallbill\Model\Mongo;

use MongoId;

class ModelMongoTest extends \PHPUnit_Framework_TestCase
{
    /** @var ModelMongo */
    protected $model;
    protected $mockMongoClient;

    protected function setUp()
    {
        parent::setUp();

        $this->mockMongoClient = \Mockery::mock('Bigtallbill\Model\Mongo\DbWrapper\DbWrapperMongo[insert,update,remove,find]');
        $this->mockMongoClient->shouldReceive('insert')->andReturn(true);
        $this->mockMongoClient->shouldReceive('update')->andReturn(true);
        $this->mockMongoClient->shouldReceive('remove')->andReturn(true);
        $this->mockMongoClient->shouldReceive('find')->andReturn(array(
            '_id' => new MongoId(),
            'some_key' => 'someValue'
        ));

        $this->model = new \Bigtallbill\Model\Mongo\ModelMongo($this->mockMongoClient, 'test', 'test', array());
    }

    public function testAddProp()
    {
        $this->model->addProp('testProp', array());
        $config = $this->model->getPropConfig();
        $this->assertArrayHasKey('testProp', $config);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMagicGetException()
    {
        $this->model->someProp;
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMagicSetExecption1()
    {
        $this->model->testProp = 'prop does not exist';
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMagicSetExecption2()
    {
        $this->model->addProp('testProp', array('types' => array('Bigtallbill\Model\Mongo\ModelMongo')));
        $this->model->testProp = 'not the correct type of data!';
    }

    public function test_magicGetSet()
    {
        $this->model->addProp('testProp', array('types' => array('Bigtallbill\Model\Mongo\ModelMongo')));

        $this->assertSame(null, $this->model->testProp, 'values are always null before they are set a value');
        $this->model->testProp = $this->model;
        $this->assertSame($this->model, $this->model->testProp, 'the correct value should be returned');
    }

    public function test_magicGetSet_unknown_properties()
    {
        $this->model = new ModelMongo($this->mockMongoClient, '', '', array(), true);
        $this->model->someProperty = 'some value';

        $this->assertSame('some value', $this->model->someProperty);
    }

    public function test_toArray()
    {
        $this->model->insert();
        $arr = $this->model->toArray();

        $this->assertArrayHasKey('_id', $arr);
    }

    public function test_toFromArray()
    {
        $this->model->insert();
        $arr = $this->model->toArray();
        $this->model->fromArray($arr);
        $this->assertArrayHasKey('_id', $arr);
    }

    public function test_applyNewId()
    {
        $actual = $this->model->applyNewId();
        $this->assertInternalType('array', $actual);
        $this->assertArrayHasKey($this->model->getIdKeyName(), $actual, 'the id should exist in the array');
        $this->assertInstanceOf('\MongoId', $actual[$this->model->getIdKeyName()]);
    }

    //--------------------------------------
    // TEST DB
    //--------------------------------------

    public function testFind()
    {
        $this->model->addProp('some_key', array('types' => array('string')));
        $this->model->loadById('because we mock the db response the id value does not matter');
        $this->assertTrue($this->model->isLoaded());
        $this->assertSame($this->model->some_key, 'someValue');
    }
}
