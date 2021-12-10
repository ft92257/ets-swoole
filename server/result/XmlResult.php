<?php
namespace Ets\server\result;

use Ets\base\BaseObject;
use Ets\helper\ToolsHelper;

class XmlResult extends BaseObject implements ResultInterface
{

    protected $data;

    protected $rootTag;

    /**
     * @Override
     * @return array
     */
    protected function allowInitFields()
    {
        return ['data', 'rootTag'];
    }

    public static function success($data, $rootTag = '')
    {
        return new XmlResult([
            'data' => $data,
            'rootTag' => $rootTag,
        ]);
    }

    /**
     * @Override
     * @return string
     */
    public function getOutputString(): string
    {
        return ToolsHelper::arrayToXml($this->data, $this->rootTag);
    }

}
