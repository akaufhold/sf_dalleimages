<?php
declare(strict_types=1);

namespace Stackfactory\SfDalleimages\Tca;

class DalleImageSizeOptions
{
    public function getSizeOptions(array &$config)
    {
        $selectedModel = $config['row']['tx_dalleimage_model'];
        $items = [];

        switch ($selectedModel) {
            case 'dall-e-1':
                $items = [
                    ['256x256', '256x256'],
                    ['512x512', '512x512'],
                    ['1024x1024', '1024x1024'],
                ];
                break;
            case 'dall-e-2':
                $items = [
                    ['256x256', '256x256'],
                    ['512x512', '512x512'],
                    ['1024x1024', '1024x1024'],
                ];
                break;
            case 'dall-e-3':
                $items = [
                    ['1024x1024', '1024x1024'],
                    ['1024x1792', '1024x1792'],
                    ['1792x1024', '1792x1024'],
                ];
                break;
            default:
                $items = [
                    ['256x256', '256x256'],
                    ['512x512', '512x512'],
                    ['1024x1024', '1024x1024'],
                ];
                break;
        }

        $config['items'] = $items;
    }
}
