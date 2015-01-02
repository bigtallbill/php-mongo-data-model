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
    /**
     * @param $id
     *
     * @return boolean True if the object was loaded, False otherwise
     */
    public function loadById($id)
    {
        $result = $this->client->find(
            new Find($this->databaseName, $this->collectionName, array($this->getIdKeyName() => $id))
        );

        if (!is_array($result)) {
            return false;
        }

        $this->fromArray($result);
        return true;
    }

    /**
     * @return string
     */
    public function getIdKeyName()
    {
        return '_id';
    }

    /**
     * @return mixed
     */
    public function update()
    {
        $object = array(
            '$set' => $this->toArray(true)
        );

        return $this->client->update(
            new Update($this->databaseName, $this->collectionName, array($this->getIdKeyName() => $this->id), $object)
        );
    }

    /**
     * @return mixed
     */
    public function insert()
    {
        $response = $this->client->insert(new Insert($this->databaseName, $this->collectionName, $this->applyNewId()));
        return $response;
    }

    /**
     * @return mixed
     */
    public function remove()
    {
        return $this->client->remove(
            new Remove($this->databaseName, $this->collectionName, array($this->getIdKeyName() => $this->id))
        );
    }

    /**
     * @return \MongoId
     */
    public function getNewId()
    {
        return new \MongoId();
    }

    /**
     * Applies a new id (generated from the getNewId method) to the id property and returns the result
     *
     * This method will modify the local ModelMongo object
     *
     * @return array
     */
    public function applyNewId()
    {
        // when a new doc is inserted get the new id back
        if ($this->id === null) {
            $this->id = $this->getNewId();
        }

        $arr = $this->toArray();
        return $arr;
    }
}
