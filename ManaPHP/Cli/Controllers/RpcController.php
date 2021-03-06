<?php

namespace ManaPHP\Cli\Controllers;

use ManaPHP\Cli\Controller;
use ManaPHP\Helper\LocalFS;
use ReflectionClass;
use ReflectionMethod;

class RpcController extends Controller
{
    /**
     * generate services stub for client
     *
     * @param string $output
     *
     * @throws \ManaPHP\Exception\RuntimeException
     */
    public function servicesCommand($output = '@tmp/rpc_services')
    {
        foreach (LocalFS::glob('@app/Controllers/*Controller.php') as $file) {
            $className = 'App\\Controllers\\' . basename($file, '.php');

            $methods = [];
            foreach (get_class_methods($className) as $method) {
                if (preg_match('#^(.*)Action$#', $method, $match)) {
                    $methods[] = $method;
                }
            }

            if ($methods !== []) {
                $content = $this->_renderService($className, $methods);
                $file = rtrim($output, '/') . '/' . basename($className, 'Controller') . 'Services.php';
                LocalFS::filePut($file, $content);

                $serviceName = basename($className, 'Controller') . 'Service';
                $this->console->writeLn("`$serviceName` saved to `$file`");
            }
        }
    }

    /**
     * @param string $class
     * @param array  $methods
     *
     * @return string
     */
    protected function _renderService($class, $methods)
    {
        $serviceName = basename($class, 'Controller') . 'Service';

        $lines = file((new ReflectionClass($class))->getFileName());
        $content = <<<EOT
<?php

namespace App\Services;

use ManaPHP\Rpc\Client\Service;

class $serviceName extends Service
{
EOT;
        foreach ($methods as $method) {
            $content .= PHP_EOL;
            $rm = new ReflectionMethod($class, $method);
            if ($doc = $rm->getDocComment()) {
                $content .= "\t" . $doc . PHP_EOL;
            }

            $signature = $lines[$rm->getStartLine() - 1];
            $content .= preg_replace('#(\s.*)Action#', '\\1', $signature);
            $content .= <<<EOT
    {
        return \$this->invoke(__METHOD__, func_get_args());
    }
EOT;
            $content .= PHP_EOL;
        }

        $content .= '}';

        return $content;
    }
}