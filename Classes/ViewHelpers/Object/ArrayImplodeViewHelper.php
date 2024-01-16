<?php

namespace Xima\XmTools\ViewHelpers\Object;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Implodes array members to string, optionally calls function on members before imploding.
 *
 * = Example =
 *
 * {namespace xmTools = Xima\XmTools\ViewHelpers}
 * <xmTools:object.ArrayImplode glue=", " array="{someArray}", functionOrKey="string|int">
 *
 * @todo Move example to external file (ArrayImplodeViewHelper.md) and include as annotation 'example'
 *
 * @author Wolfram Eberius <woe@xima.de>, Steve Lenz <steve.lenz@xima.de>
 * @return string
 */
class ArrayImplodeViewHelper extends AbstractViewHelper
{

    public function initializeArguments()
    {
        $this->registerArgument('glue', 'string', 'Specifies what to put between the array elements', true);
        $this->registerArgument('array', 'array', 'Arrays to join to a string', true);
        $this->registerArgument('functionOrKey', 'string', 'Key to specify', false, '');
    }

    /**
     * Basically equal to PHP implode(). If array items are array themselves a key ($functionOrKey) can be specified.
     * If array items are objects a function to retrieve a certain value for the implode can be specified ($functionOrKey).
     *
     * @return string
     */
    public function render()
    {
        $glue = $this->arguments['glue'];
        $array = $this->arguments['array'];
        $functionOrKey = $this->arguments['functionOrKey'];
        $theArray = [];

        foreach ($array as $value) {
            if ($functionOrKey != '') {
                if (is_array($value)) {
                    $value = $value[$functionOrKey];
                } else {
                    $getter = 'get' . GeneralUtility::underscoredToUpperCamelCase($functionOrKey);
                    if (method_exists($value, $getter)) {
                        $value = $value->$getter();
                    } else {
                        $value = $value->{$functionOrKey};
                    }
                }
            }
            $theArray[] = trim($value);
        }

        return implode($glue, $theArray);
    }
}
