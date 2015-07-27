<?php

namespace jaslin\yii2\composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class ComposerScripts 
{
    const PSR4_FILE = '/composer/autoload_psr4.php';
    const EXTENSION_FILE = '/yiisoft/extensions.php';

    public static function postAutoloadDump(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $extra = $event->getComposer()->getPackage()->getExtra();
        $localPsr4Config = array_key_exists('local-psr-4',$extra) ? $extra['local-psr-4'] : null ;
        $localExtensions = array_key_exists('local-yii2-extensions',$extra) ? $extra['local-yii2-extensions'] : null;

        if($localExtensions){
            $extensions = self::loadExtensions($vendorDir);
            $extensions = array_merge($extensions,$localExtensions); 
            self::saveExtensions($vendorDir,$extensions);
        }
        if($localPsr4Config){
            self::savePsr4s($vendorDir,$localPsr4Config,$event->getIO());
        }
    }

    public static function savePsr4s($vendorDir,$localPsr4Config,$io){

        $filesystem = new FileSystem();
        $psr4File = $filesystem->normalizePath(realpath($vendorDir.self::PSR4_FILE));

        if($localPsr4Config && $psr4File){
            $io->write('generating local autoload_psr4.php ....');
            self::appendBeforeLastline($localPsr4Config,$psr4File);
            $io->write('local autoload_psr4 generated.');
        }
    }
    /**
     *
     * append the giving data to the file before the last line  
     */
    public static function appendBeforeLastline($data,$file)
    {
        $content = file_get_contents($file);

        $lines = explode("\n",$content);
        array_splice($lines,count($lines)-2,0,$data);
        $content  = implode("\n",$lines);

        file_put_contents($file,$content);
    }

    public static function loadExtensions($vendorDir)
    {
        $file = $vendorDir . self::EXTENSION_FILE;
        if (!is_file($file)) {
            return [];
        }
        // invalidate opcache of extensions.php if exists
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
        $extensions = require($file);

        $vendorDir = str_replace('\\', '/', $vendorDir);
        $n = strlen($vendorDir);

        foreach ($extensions as &$extension) {
            if (isset($extension['alias'])) {
                foreach ($extension['alias'] as $alias => $path) {
                    $path = str_replace('\\', '/', $path);
                    if (strpos($path . '/', $vendorDir . '/') === 0) {
                        $extension['alias'][$alias] = '<vendor-dir>' . substr($path, $n);
                    }
                }
            }
        }

        return $extensions;
    }

    protected static function saveExtensions($vendorDir,array $extensions)
    {
        $file = $vendorDir .self::EXTENSION_FILE;
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        $array = str_replace("'<vendor-dir>", '$vendorDir . \'', var_export($extensions, true));
        // $array = var_export($extensions, true);
        file_put_contents($file, "<?php\n\n\$vendorDir = dirname(__DIR__);\n\nreturn $array;\n");
        // invalidate opcache of extensions.php if exists
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }
}
