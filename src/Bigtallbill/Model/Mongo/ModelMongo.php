<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 24/12/14
 * Time: 21:53
 */

namespace Bigtallbill\Model\Mongo;

use Bigtallbill\Model\AModel;
use Bigtallbill\Model\Mongo\DbWrapper\Options\Find;
use Bigtallbill\Model\Mongo\DbWrapper\Options\Insert;
use Bigtallbill\Model\Mongo\DbWrapper\Options\Remove;
use Bigtallbill\Model\Mongo\DbWrapper\Options\Update;

class ModelMongo extends AModel
{
    public function loadById($id)
    {
        $result = $this->client->find(
            new Find($this->databaseName, $this->collectionName, array($this->getIdKeyName() => $id))
        );
        $this->fromArray($result);
    }

    public function getIdKeyName()
    {
        return '_id';
    }

    public function update()
    {
        $object = array(
            '$set' => $this->toArray(true)
        );

        return $this->client->update(
            new Update($this->databaseName, $this->collectionName, array($this->getIdKeyName() => $this->id), $object)
        );
    }

    public function insert()
    {
        $arr = $this->toArray();

        // when a new doc is inserted get the new id back
        if ($this->id === null) {
            $this->id = $this->getNewId();
        }

        $response = $this->client->insert(new Insert($this->databaseName, $this->collectionName, $arr));
        return $response;
    }

    public function remove()
    {
        return $this->client->remove(
            new Remove($this->databaseName, $this->collectionName, array($this->getIdKeyName() => $this->id))
        );
    }

    public function getNewId()
    {
        return new \MongoId();
    }
}