<?php

use Composer\Autoload\ClassLoader;

class ArenaPl_Magento_AutoloadInjector
{
    /**
     * Loads Composer autoloader.
     *
     * @return bool true if successful
     */
    public function injectComposerAutoloader()
    {
        /* @var $loader ClassLoader */
        $loader = (require_once $this->getAutoloadFileToRequire());

        if (is_object($loader) && $loader instanceof ClassLoader) {
            return $this->checkIfComposerIsCorrectlyAutoloaded($loader);
        }

        return false;
    }

    /**
     * Returns full path to Composer autoload file.
     *
     * @return string
     */
    protected function getAutoloadFileToRequire()
    {
        return __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Checks if Composer autoloader is loaded before Magento autoloader.
     *
     * @param ClassLoader $loader
     *
     * @return bool
     */
    protected function checkIfComposerIsCorrectlyAutoloaded(ClassLoader $loader)
    {
        $loaderObjectHash = spl_object_hash($loader);
        $mageAutoloadHash = spl_object_hash(Varien_Autoload::instance());

        $composerPos = null;
        $magentoPos = null;

        foreach (spl_autoload_functions() as $key => $autoloadData) {
            $autoloader = is_array($autoloadData) ? current($autoloadData) : $autoloadData;
            $autoloaderHash = spl_object_hash($autoloader);

            if ($autoloaderHash === $loaderObjectHash) {
                $composerPos = $key;
            } elseif ($autoloaderHash === $mageAutoloadHash) {
                $magentoPos = $key;
            }

            if ($composerPos !== null && $magentoPos !== null) {
                return $composerPos < $magentoPos;
            }
        }

        return false;
    }
}
