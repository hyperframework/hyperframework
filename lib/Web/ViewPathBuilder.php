<?php
namespace Hyperframework\Web;

use Hyperframework\Common\Config;

class ViewPathBuilder {
    /**
     * @param string $name
     * @param string $outputFormat
     * @return string
     */
    public static function build($name, $outputFormat = null) {
        $result = str_replace('-', '_', $name);
        if (Config::getBool(
            'hyperframework.web.view.filename.include_output_format', true
        )) {
            if ($outputFormat === null) {
                $outputFormat = Config::getString(
                    'hyperframework.web.view.default_output_format', 'html'
                );
            }
            $outputFormat = (string)$outputFormat;
            if ($outputFormat !== '') {
                $result .= '.' . $outputFormat;
            }
        }
        $format = Config::getString('hyperframework.web.view.format', 'php');
        if ($format !== '') {
            $result .= '.' . $format;
        }
        return $result;
    }
}
