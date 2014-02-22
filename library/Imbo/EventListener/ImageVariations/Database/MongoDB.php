<?php
/**
 * This file is part of the Imbo package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace Imbo\EventListener\ImageVariations\Database;

use Imbo\Model\Image,
    Imbo\Model\Images,
    Imbo\Resource\Images\Query,
    Imbo\Exception\DatabaseException,
    MongoClient,
    MongoCollection,
    MongoException;

/**
 * MongoDB database driver for the image variations
 *
 * Valid parameters for this driver:
 *
 * - (string) databaseName Name of the database. Defaults to 'imbo'
 * - (string) server The server string to use when connecting to MongoDB. Defaults to
 *                   'mongodb://localhost:27017'
 * - (array) options Options to use when creating the MongoClient instance. Defaults to
 *                   array('connect' => true, 'connectTimeoutMS' => 1000).
 *
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @package Database
 */
class MongoDB implements DatabaseInterface {
    /**
     * Mongo client instance
     *
     * @var MongoClient
     */
    private $mongoClient;

    /**
     * The imagevariation collection
     *
     * @var MongoCollection
     */
    private $collection;

    /**
     * Parameters for the driver
     *
     * @var array
     */
    private $params = array(
        // Database name
        'databaseName' => 'imbo',

        // Server string and ctor options
        'server'  => 'mongodb://localhost:27017',
        'options' => array('connect' => true, 'connectTimeoutMS' => 1000),
    );

    /**
     * Class constructor
     *
     * @param array $params Parameters for the driver
     * @param MongoClient $client MongoClient instance
     * @param MongoCollection $collection MongoCollection instance for the image variation collection
     */
    public function __construct(array $params = null, MongoClient $client = null, MongoCollection $collection = null) {
        if ($params !== null) {
            $this->params = array_replace_recursive($this->params, $params);
        }

        if ($client !== null) {
            $this->mongoClient = $client;
        }

        if ($collection !== null) {
            $this->collection = $collection;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function storeImageVariationMetadata($publicKey, $imageIdentifier, $width, $height) {
        try {
            $this->getCollection()->insert(array(
                'added' => time(),
                'publicKey' => $publicKey,
                'imageIdentifier'  => $imageIdentifier,
                'width' => $width,
                'height' => $height,
            ));
        } catch (MongoException $e) {
            throw new DatabaseException('Unable to save image variation data', 500, $e);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getBestMatch($publicKey, $imageIdentifier, $width) {
        $query = array(
            'publicKey' => $publicKey,
            'imageIdentifier' => $imageIdentifier,
            'width' => array(
                '$gte' => $width,
            ),
        );

        $cursor = $this->getCollection()->find($query, array('_id' => false, 'width', 'height'))
                                        ->limit(1)
                                        ->sort(array('width' => 1));

        return $cursor->getNext();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteImageVariations($publicKey, $imageIdentifier, $width = null) {
        $query = array(
            'publicKey' => $publicKey,
            'imageIdentifier' => $imageIdentifier,
        );

        if ($width !== null) {
            $query['width'] = $width;
        }

        $this->getCollection()->remove($query);

        return true;
    }

    /**
     * Get the mongo collection
     *
     * @return MongoCollection
     */
    private function getCollection() {
        if ($this->collection === null) {
            try {
                $this->collection = $this->getMongoClient()->selectCollection(
                    $this->params['databaseName'],
                    'imagevariation'
                );
            } catch (MongoException $e) {
                throw new DatabaseException('Could not select collection', 500, $e);
            }
        }

        return $this->collection;
    }

    /**
     * Get the mongo client instance
     *
     * @return MongoClient
     */
    private function getMongoClient() {
        if ($this->mongoClient === null) {
            try {
                $this->mongoClient = new MongoClient($this->params['server'], $this->params['options']);
            } catch (MongoException $e) {
                throw new DatabaseException('Could not connect to database', 500, $e);
            }
        }

        return $this->mongoClient;
    }
}
