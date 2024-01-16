<?php

namespace Xima\XmTools\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Xima\XmTools\Domain\Repository\CategoryRepository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 OpenSource Team, XIMA MEDIA GmbH, osdev@xima.de
 *  Inspird by http://blog.systemfehler.net/erweiterung-des-typo3-kategoriensystems/
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class GetCategoriesViewHelper extends AbstractViewHelper
{
    /**
     * @var CategoryRepository
     * */
    protected $categoryRepository;

    public function initializeArguments()
    {
        $this->registerArgument('parentCategory', 'integer', 'The parent category', true, 0);
        $this->registerArgument('excludeCategories', 'string', 'Exclude categories (comma separated list of uids)', false);
        $this->registerArgument('firstOptionLabel', 'string', 'What should be the label of the first option (with value = 0)? Possible values: "parent" (the title of parent category), "none" (no option with value = 0 will be rendered), <your_own_string> (any custom string)', false, 'parent');
        $this->registerArgument('as', 'string', 'Name of the template variable that will contain the categories', true);
    }

    public function injectCategoryRepository(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Return child categories
     *
     * @return mixed
     * @api
     */
    public function render()
    {
        $parent = $this->categoryRepository->findByUid($this->arguments['parentCategory']);
        $excludeCategories = ($this->arguments['excludeCategories'] ? explode(',', $this->arguments['excludeCategories']) : []);
        $children = $this->categoryRepository->findChildrenByParent($this->arguments['parentCategory'], $excludeCategories);
        $firstOptionLabel = $this->arguments['firstOptionLabel'];
        $as = (string)$this->arguments['as'];
        $options = []; // for dropdown select

        if ($firstOptionLabel == 'none') {
        } elseif ($firstOptionLabel == 'parent') {
            $options[0] = $parent->getTitle();
        } else {
            $options[0] = $firstOptionLabel;
        }

        foreach ($children as $child) {
            $options[$child->getUid()] = $child->getTitle();
        }

        $this->templateVariableContainer->add($as, [
            'parent' => $parent,
            'children' => $children,
            'options' => $options,
        ]);

        $output = $this->renderChildren();
        $this->templateVariableContainer->remove($as);

        return $output;
    }
}
