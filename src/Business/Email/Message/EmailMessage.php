<?php

namespace App\Business\Email\Message;

use BadMethodCallException;
use InvalidArgumentException;

/**
 * 电子邮件消息
 */
abstract class EmailMessage
{
    /**
     * 模板参数
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * 模板变量参数规则
     *
     * @var array
     */
    protected $templateArgumentRules = [];

    /**
     * @param string $data
     *
     * @return mixed
     */
    abstract public static function format(string $data);

    /**
     * 参数检验
     */
    public function argumentCheck()
    {
        foreach ($this->templateArgumentRules as $key => $rule) {
            $require = $rule['require'] ?? null;
            $default = $rule['default'] ?? null;
            if ($require && (!isset($this->arguments[$key]) || empty($this->arguments[$key]))) {
                throw new InvalidArgumentException(sprintf('param [%s] is required in this template!', $key));
            }
            if ($default && !isset($this->arguments[$key])) {
                $this->arguments[$key] = $rule['default'];
            }
            $currentParam = $this->arguments[$key];
            $type         = $rule['type'] ?? 'string';
            switch ($type) {
                case 'string':

                    if (!is_string($currentParam)) {
                        throw new InvalidArgumentException(sprintf('param [%s] \'s type is error,must set to %s!', $key, $rule['type']));
                    }
                    break;
                case 'int':

                    if (false !== filter_var($currentParam, FILTER_VALIDATE_INT)) {
                        throw new InvalidArgumentException(sprintf('param [%s] \'s type is error,must set to %s!', $key, $rule['type']));
                    }
                    break;
                case 'enum':

                    if (empty($rule['enum_params'])) {
                        throw new BadMethodCallException(sprintf('you need define param %s \'s enum params set!', $key));
                    }

                    if (!in_array($currentParam, $rule['enum_params'])) {
                        throw new InvalidArgumentException(sprintf('param [%s]  is out of enum params set , please check it!', $key));
                    }

                    break;
            }
        }
    }
}
