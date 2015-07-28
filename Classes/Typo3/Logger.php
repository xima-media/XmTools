<?php
namespace Xima\XmTools\Classes\Typo3;

class Logger implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var \Xima\XmTools\Classes\Typo3\Extension\ExtensionManager
     * @inject
     */
    protected $extensionManager;
    
    /**
     * 
     * @var $logger \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;
    
    protected $isEnabled;
    
    public function initializeObject()
    {
        $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        $this->isEnabled = $this->extensionManager->getXmTools()->getSettings()['loggingIsEnabled'];
    }
    
    public function log($text, \Xima\XmTools\Classes\Typo3\Model\Extension $extension = null)
    {
        if ($this->isEnabled)
        {
            if (!is_null($extension))
            {
                $text = $extension->getName().': '.$text;
            }
            $this->logger->info($text);
        }
    }
    
}