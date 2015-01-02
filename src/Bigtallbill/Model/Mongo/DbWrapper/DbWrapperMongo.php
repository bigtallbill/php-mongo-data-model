<?php
/**
 * Created by PhpStorm.
 * User: bigtallbill
 * Date: 24/12/14
 * Time: 17:56
 */

namespace Bigtallbill\Model\Mongo\DbWrapper;


use Bigtallbill\Model\DbWrapper\ADbWrapper;
use Bigtallbill\Model\DbWrapper\OperationOptions;
use Bigtallbill\Model\DbWrapper\Options\Find;
use Bigtallbill\Model\DbWrapper\Options\Insert;
use Bigtallbill\Model\DbWrapper\Options\Update;
use MongoClient;

class DbWrapperMongo extends ADbWrapper
{
    /**
     * @var MongoClient
     */
    protected $conn;

    /** @var mixed */
    protected $lastError;

    /**
     * @param $client
     */
    public function setConnection($client)
    {
        $this->conn = $client;
    }

    /**
     * @param Find $options
     * @return array|\MongoCursor|null
     */
    public function find(OperationOptions $options)
    {
        // do a findOne if only a specific _id is requested (much faster)
        if (count($options->query) === 1 && array_key_exists('_id', $options->query)) {
            $result = $this->conn->{$options->db}->{$options->col}->findOne($options->query, $options->options);
            $this->isResponseOk($result);
            return $result;
        }

        $result = $this->conn->{$options->db}->{$options->col}->find($options->query, $options->options);
        $this->isResponseOk($result);
        return $result;
    }

    /**
     * @param Insert $options
     * @return array|bool
     */
    public function insert(OperationOptions $options)
    {
        $result = $this->conn->{$options->db}->{$options->col}->insert($options->object, $options->options);
        $this->isResponseOk($result);
        return $result;
    }

    /**
     * @param Update $options
     * @return bool
     */
    public function update(OperationOptions $options)
    {
        $result = $this->conn->{$options->db}->{$options->col}->update($options->query, $options->object, $options->options);
        $this->isResponseOk($result);
        return $result;
    }

    /**
     * @param OperationOptions $options
     * @return array|bool
     */
    public function remove(OperationOptions $options)
    {
        $result = $this->conn->{$options->db}->{$options->col}->remove($options->query, $options->options);
        $this->isResponseOk($result);
        return $result;
    }

    /**
     * @param $response
     * @return bool
     */
    protected function isResponseOk($response)
    {
        if ($response === null) {
            return true;
        }

        if ($response === true) {
            return true;
        }

        if ($response instanceof \MongoCursor) {
            return true;
        }

        if (is_array($response)) {
            if (array_key_exists('ok', $response) && $response['ok'] == 1) {
                return true;
            } elseif (array_key_exists('err', $response) && !is_null($response['err'])) {
                $this->lastError = $response;
                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function lastError()
    {
        return $this->lastError;
    }
}
