<?php

namespace jaslin\yii2\composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class ComposerScripts 
{
    const PSR4_FILE = '/composer/autoload_psr4.php';
    const YII2_EXTENSION_FILE = '/yiisoft/extensions.php';

    public static function postAutoloadDump(Event $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();
        $filesystem = new FileSystem();
        $config = $composer->getConfig();
        $package = $composer->getPackage();
        $extra = $package->getExtra();
        $localPsr4Config = $extra['local-psr-4'];
        $vendorDir  = $config->get('vendor-dir');
        $oExtensions = self::loadExtensions($vendorDir);
        $yii2LocalExtensions = $extra['local-yii2-extensions'];
        

        $localYii2Config = '';
        foreach($yii2LocalExtensions as $extension){
            if(array_key_exists($extension['name'],$oExtensions)){
                $localYii2Config .= self::genYii2ExtensionConfig($extension);
            } 
        }
        $psr4File = $filesystem->normalizePath(realpath($config->get('vendor-dir').self::PSR4_FILE));
        $yii2extensionFile = $filesystem->normalizePath(realpath($config->get('vendor-dir').self::YII2_EXTENSION_FILE));
        
        if($localPsr4Config && $psr4File){
            $io->write('generating local autoload_psr4.php ....');
            self::appendBeforeLastline($localPsr4Config,$psr4File);
            $io->write('local autoload_psr4 generated.');
        }
        if($localYii2Config && $yii2extensionFile){
            $io->write('generating local yii2 extensions.php....');
            self::appendBeforeLastline($localYii2Config,$yii2extensionFile);
            $io->write('local yii2 extensions.php generated.');
        }
    }
    /**
     *
     * generate an yii2 extension config string  
     *   *
     * @param array $extension an extension object ,it should be in the format like  ['name'=>'','version'=>'','alias'=>'','path'=>'']
     */
    public static function genYii2ExtensionConfig($extension)
    {
        $template = "
    '{name}' => 
    array(
        'name' => '{name}',
        'version' => '{version}',
        'alias' => 
            array(
                '{alias}'=>{path}
            )
    ),";
        $search = array('{name}','{version}','{alias}','{path}');
        $config = str_replace($search,$extension,$template);
        return $config;
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
        $file = $vendorDir . self::YII2_EXTENSION_FILE;
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
}
