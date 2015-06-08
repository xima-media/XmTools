<?php

namespace Xima\XmTools\Classes\API\REST;

use Xima\XmTools\Classes\Helper\Helper;

/**
 * The Api facade. The API configuration must be done through the TYPO3 constant editor for the concrete extension.
 *
 * @author Wolfram Eberius <woe@xima.de>
 */
class Connector
{
    /**
     * The extension that uses the Connector.
     *
     * @var \Xima\XmTools\Classes\Typo3\Model\Extension
     */
    protected $extension;

    /**
     * The cache manager for retrieved data.
     *
     * @var \Xima\XmTools\Classes\Typo3\Cache\ApiCacheManager
     * @inject
     */
    protected $cacheManager;

    /**
     * Gets called by repositories inheriting from \Xima\XmTools\Classes\API\REST\Repository\AbstractApiRepository, retrieves JSON responses, converts
     * arrays to objects according to the given repository class name (if existing, also parses to class properties) or to array of arrays.
     * Translates values to the current or fallback language when fields with the following patterns are found:
     * -nameDe, nameEn...
     * -name_de, name_en...
     * Calls cache or calls API and stores result in cache if older than one day.
     *
     * @param string                                                          $url
     * @param \Xima\XmTools\Classes\API\REST\Repository\AbstractApiRepository $repository
     * @param array                                                           $params
     *
     * @return array
     */
    public function get($url, \Xima\XmTools\Classes\API\REST\Repository\ApiRepository $repository, $params = array())
    {
        $repositoryClassName = get_class($repository);
        $modelClassName = str_replace('\Repository', '\Model', $repositoryClassName);
        $modelClassName = str_replace('Repository', '', $modelClassName);

        $isApiCacheEnabled = $this->extension->getSettings()['api']['isCacheEnabled'];
        $fallbackLanguage = $this->extension->getSettings()['fallbackLanguage'];

        $responseJson = false;
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $logger = $objectManager->get('Xima\XmTools\Classes\Typo3\Logger');

        $logger->log('Called api url: '.$url);

        // retrieve data from cache or api
        if ($isApiCacheEnabled) {
            $responseJson = $this->cacheManager->get($url);

            if ($responseJson) {
                $logger->log('Got api data from cache.');
            }
        }

        if (!$responseJson) {
            $logger->log('Try to get data from api.');
            $responseJson = file_get_contents($url);
        }

        $response = json_decode($responseJson, true);

        if (array_key_exists('result', $response)) {
            $logger->log('Success.');

            //write to api
            if ($isApiCacheEnabled) {
                $this->cacheManager->write($url, $responseJson);
            }

            $response ['result'] = Helper::translate($response ['result'], $response ['metadata']['lang'], $fallbackLanguage);

            // if it is a single result and a single result was queried we still want to return an array of arrays
            if (!is_int(array_shift(array_keys($response ['result']))) && isset($response ['result'] ['id'])) {
                $response ['result'] = array(
                    $response ['result'] ['id'] => $response ['result'], );
            }

            if (class_exists($modelClassName)) {
                $result = array();

                foreach ($response ['result'] as $key => $data) {
                    if (isset($data['id'])) {
                        // make the entities fit typo3 better
                        $data ['uid'] = $data['id'];
                    }

                    $result [$key] = new $modelClassName ();
                    if (is_a($result [$key], 'Xima\XmTools\Classes\API\REST\Model\AbstractEntity')) {
                        $result [$key]->parsePropertyArray($data);
                    }
                }

                $response ['result'] = $result;
            }
        } else {
            $errorMessage = 'Api data not available for extension \''.$this->extension->getName().'\'';
            trigger_error($errorMessage, E_USER_WARNING);
            $logger->log($errorMessage);

            $response ['result'] = array();
        }

        return $response;
    }

    /**
     * Sets the current extension and the cache path accoring to the extension key.
     *
     * @param \Xima\XmTools\Classes\Typo3\Model\Extension
     *
     * @return \Xima\XmTools\Classes\API\REST\Connector
     */
    public function setExtension(\Xima\XmTools\Classes\Typo3\Model\Extension $extension)
    {
        $this->extension = $extension;
        $this->cacheManager->setPath($extension->getKey());

        return $this;
    }
}
