<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace XLite\Module\XC\ThemeTweaker\Model;

class Product extends \XLite\Model\Product implements \XLite\Base\IDecorator
{
    /**
     * Checks if given property is available to modification through layout editor mode.
     *
     * @param  string  $property Checked entity property
     * @return boolean
     */
    public function isEditableProperty($property)
    {
        $editable = array('description', 'briefDescription');

        return in_array($property, $editable, true);
    }

    /**
     * Provides metadata for the property
     *
     * @param  string  $property Checked entity property
     * @return array
     */
    public function getFieldMetadata($property)
    {
        return array_merge(
            parent::getFieldMetadata($property),
            array(
                'data-inline-editable' => 'data-inline-editable',
            )
        );
    }

    /**
     * This is config for layout editor mode editor.
     *
     * @return array
     */
    public function getInlineEditorConfig()
    {
        $config = [
            'toolbarInline'                  => true,
            'toolbarVisibleWithoutSelection' => true,
            'charCounterCount'               => false,
            'imageUploadURL'                 => $this->getImageUploadURL(),
            'imageManagerLoadURL'            => $this->getImageManagerLoadURL(),
            'imageManagerDeleteURL'          => $this->getImageManagerDeleteURL(),
            'imageUploadParam'               => 'file',
            'imageUploadParams'              => [
                'url_param_name' => 'link',
            ],
            'zIndex'                         => 9990,
            'requestHeaders'                 => [
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'toolbarButtons'                 => $this->getToolbarButtons(),
            'toolbarButtonsMD'               => $this->getToolbarButtons(),
            'toolbarButtonsSM'               => $this->getToolbarButtons(),
            'toolbarButtonsXS'               => $this->getToolbarButtons(),
        ];

        if($this->useCustomColors() && $this->getCustomColors()) {
            $config['colorsBackground'] = $this->getCustomColors();
            $config['colorsText'] = $this->getCustomColors();
            $config['colorsStep'] = 6;
        }

        return $config;
    }


    protected function getToolbarButtons()
    {
        return [
            'fontFamily', 'fontSize',
            '|', 'bold', 'italic', 'underline', 'strikeThrough', 'color',
            '-', 'paragraphFormat', 'paragraphStyle', 'align', 'formatOL', 'formatUL',
            '|', 'indent', 'outdent',
            '-', 'insertImage', 'insertTable', 'insertLink', 'insertVideo',
            '|', 'undo', 'redo', 'html'
        ];
    }

    protected function getImageUploadURL()
    {
        $params = [
            'mode'  => 'json',
            'type'  => 'image',
        ];

        return \XLite\Core\Converter::buildFullURL('files', 'upload_from_file', $params, \XLite::getAdminScript());
    }

    protected function getImageManagerLoadURL()
    {
        return \XLite\Core\Converter::buildFullURL('files', 'get_image_manager_list', [], \XLite::getAdminScript());
    }

    protected function getImageManagerDeleteURL()
    {
        return \XLite\Core\Converter::buildFullURL('files', 'remove_from_image_manager', [], \XLite::getAdminScript());
    }


    /**
     * @return bool
     */
    protected function useCustomColors()
    {
        return (bool) \XLite\Core\Config::getInstance()->XC->FroalaEditor->use_custom_colors;
    }

    /**
     * @return array
     */
    protected function getCustomColors()
    {
        $customColors = [];

        $colorsSetting = \XLite\Core\Config::getInstance()->XC->FroalaEditor->custom_colors;
        if ($colorsSetting) {
            $customColors = explode(',', $colorsSetting);

            $customColors = array_map(function($color) {
                return '#' . $color;
            }, $customColors);
        }

        $customColors[] = 'REMOVE';

        return $customColors;
    }
}
