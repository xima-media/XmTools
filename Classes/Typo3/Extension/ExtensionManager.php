<?php
namespace Xima\XmTools\Classes\Typo3\Extension;

class ExtensionManager implements \TYPO3\CMS\Core\SingletonInterface
{
    const XmTools = 'XmTools';
    /**
     *
     * @var \Xima\XmTools\Classes\Typo3\Extension\ExtensionHelper
     * @inject
     */
    protected $extensionHelper;

    protected $extensions = array();

    protected $currentExtensionName = null;

    /**
     * @param  string                                      $extensionName
     * @return \Xima\XmTools\Classes\Typo3\Model\Extension $extension
     *
     */
    public function getExtensionByName($extensionName)
    {
        $extension = false;

        if (isset($this->extensions[$extensionName]) && is_a($this->extensions[$extensionName], 'Xima\XmTools\Classes\Typo3\Model\Extension')) {
            $extension = $this->extensions[$extensionName];
        } else {
            $extension = $this->extensionHelper->getExtension($extensionName);
            if (is_a($extension, 'Xima\XmTools\Classes\Typo3\Model\Extension')) {
                $this->extensions[$extension->getName()] = $extension;
            }
        }

        return $extension;
    }

    public function getCurrentExtension()
    {
        $extension = false;

        if (is_null($this->currentExtensionName)) {
            $extension = $this->extensionHelper->getExtension();
            if ($extension) {
                $this->currentExtensionName = $extension->getName();
                $this->extensions[$this->currentExtensionName] = $extension;
            }
        }

        return $extension;
    }

    public function getXmTools()
    {
        return $this->getExtensionByName(ExtensionManager::XmTools);
    }
}
