<?php
/* REQUIRES PHP 7.x AT LEAST */
namespace aeSecure;

class Settings
{

    protected static $instance = null;

    private $folderDocs='';     // subfolder f.i. 'docs' where markdown files are stored (f.i. "Docs\")
    private $folderAppRoot='';

    private $json=array();

    private $aeDebug=null;
    private $aeJSON=null;

    public function __construct(string $folder)
    {

        include_once 'constants.php';

        /*<!-- build:debug -->*/
        if (!class_exists('Debug')) {
            include_once 'debug.php';
        }
        $this->aeDebug=\AeSecure\Debug::getInstance();
        /*<!-- endbuild -->*/

        if (!class_exists('JSON')) {
            include_once 'json.php';
        }

        $this->aeJSON=\AeSecure\JSON::getInstance();

        $this->setFolderAppRoot(dirname(dirname(__FILE__)));
        if ($folder!=='') {
            $this->setFolderWebRoot($folder);
        }
        $this->setFolderDocs(DOC_FOLDER);

        self::readSettings();

        return true;
    } // function __construct()

    public static function getInstance(string $folder = '')
    {

        if (self::$instance === null) {
            self::$instance = new Settings($folder);
        }

        return self::$instance;
    } // function getInstance()

  /**
    * Read the user's settings i.e. the file "settings.json"
    * Initialize class properties
    * @return {bool [description]
    */
    private function readSettings() : bool
    {

        if (!class_exists('Files')) {
            include_once 'files.php';
        }

        if (\AeSecure\Files::fileExists($fname = $this->getFolderWebRoot().'settings.json.dist')) {
            $this->json=$this->aeJSON->json_decode($fname, true);
        } else {
            if (\AeSecure\Files::fileExists($fname = $this->getFolderAppRoot().'settings.json.dist')) {
                $this->json=$this->aeJSON->json_decode($fname, true);
            } else {
                $this->json=array();
            }
        }

        if (\AeSecure\Files::fileExists($fname = $this->getFolderWebRoot().'settings.json')) {
            $this->json=array_merge($this->json, $this->aeJSON->json_decode($fname, true));
        }

        if (isset($this->json['language'])) {
            $this->setLanguage($this->json['language']);
        } else {
            $this->setLanguage(DEFAULT_LANGUAGE);
        }

        /*<!-- build:debug -->*/
        if (isset($this->json['debug'])) {
            $this->setDebugMode($this->json['debug']==1?true:false); // Debug mode enabled or not
        }
        if (isset($this->json['development'])) {
            $this->setDevMode($this->json['development']==1?true:false); // Developer mode enabled or not
        }
        /*<!-- endbuild -->*/

        /*

                if (!in_array($this->getFolderDocs(), $this->settingsFoldersAutoOpen)) {
                    array_push($this->settingsFoldersAutoOpen, $this->getFolderDocs());
                }

                asort($this->settingsFoldersAutoOpen);

                // Retrieve the password if mentionned
                if (isset($this->json['password'])) {
                    $this->settingsPassword=$this->json['password'];
                }
        */

        return true;
    } // function ReadSettings()

    /**
    * Return the translation of a given text
     *
    * @param string $variable
    */
    public function getText(string $variable, string $default = '', bool $jsProtect = false) : string
    {

        $lang=&$this->json['languages'][$this->getLanguage()];
        $return=isset($lang[$variable]) ? $lang[$variable] : '';

        if ($return=='') {
            $lang=&$this->json['languages'][DEFAULT_LANGUAGE];
            $return=isset($lang[$variable]) ? $lang[$variable] : '';
        }

        if ($return=='') {
            $return=(trim($default)!=='' ? constant($default) : '');
        }

        if ($jsProtect) {
            $return=str_replace("'", "\'", html_entity_decode($return));
        }

        // In case of null (i.e. the translation wasn't found, return at least the name of the variable)
        return ($return===null?$variable:$return);
    } // function getText()

    /**
     * Small sanitization function to be sure that the user willn't type anything in the settings.json file
     * for filename properties
     *
     * @param  string $fname
     * @return string
     */
    private function sanitizeFileName(string $fname) : string
    {

        $fname=trim($fname);

        if ($fname!=='') {
            // should only contains letters, figures or dot/minus/underscore
            if (!preg_match('/^[A-Za-z0-9-_\.]+$/', $fname)) {
                $fname='';
            }
        } // if ($fname!=='')

        return $fname;
    } // function sanitizeFileName()

    /**
    * Open the package.json file and retrieve an information like the version number from there
    *
    * @return string
    */
    private function getPackageInfo(string $info = 'version') : string
    {

        $aeJSON=\AeSecure\JSON::getInstance();
        if (\AeSecure\Files::fileExists($fname = $this->getFolderAppRoot().'package.json')) {
            $json=$aeJSON->json_decode($fname, true);
            return $json[$info];
        } else {
            return '';
        }
    } // function getPackageInfo()

    /**
     * Return the name of the program, with or without its version number (from package.json)
     *
     * @param  bool $addVersionNumber
     * @return string
     */
    public function getAppName(bool $addVersionNumber = false) : string {
        $name=APP_NAME;
        if ($addVersionNumber) {
            $name.=' v.'.self::getPackageInfo('version');
        }
        return $name;
    } // function getAppName()

    /**
     * Return the homepage of the program (from package.json)
     *
     * @return string
     */
    public function getAppHomepage() : string
    {
        return self::getPackageInfo('homepage');
    } // function getAppHomepage()

    public function getLanguage() : string
    {
        return $this->language;
    }

    public function setLanguage(string $lang)
    {

        if (isset($this->json['languages'][$lang])) {
            // The language should exists in the settings.json languages node
            // If not, use the default one
            $this->language=$lang;
        } else {
            $this->language=DEFAULT_LANGUAGE;
        }
    }

    public function getDebugMode() : bool
    {
        return $this->debugmode ? true : false;
    } // function getDebugMode()

    public function setDebugMode(bool $onOff) {

        $this->debugmode=false;
        error_reporting(0);

        /*<!-- build:debug -->*/
        if ($onOff) {
            $this->debugmode=$onOff; // Debug mode enabled or not

            if (isset($this->aeDebug)) {
                $this->aeDebug->enable();
            }
            $this->aeJSON->debug(true);
        } // if ($onOff)
        /*<!-- endbuild -->*/
    } // function setDebugMode()

    public function getDevMode() : bool
    {
        return $this->devmode ? true : false;
    } // function getDevMode()

    /**
     * Set the developper mode
     *
     * @param bool $onOff
     */
    public function setDevMode(bool $onOff)
    {

        $this->devmode=false;

        /*<!-- build:debug -->*/
        // Only when the development mode is enabled, include php_error library to make life easier
        if ($onOff)
        {
            if (\AeSecure\Files::fileExists($lib = $this->getFolderLibs().'php_error'.DS.'php_error.php'))
            {
                // Seems to not work correctly with ajax; the return JSON isn't correctly understand by JS
                $options = array(
                  // Don't enable ajax is not ajax call
                  'catch_ajax_errors' => \AeSecure\Functions::isAjaxRequest(),
                  // Don't allow to modify sources from php-error
                  'enable_saving' => 0,
                   // Capture everything
                  'error_reporting_on' => 1
                );

                include $lib;
                \php_error\reportErrors($options);
            }
        } // if ($onOff)
        /*<!-- endbuild -->*/
    } // function setDevMode()

    /**
     * The application root folder (due to the use of symbolic links, the .php source files can
     * be in an another folder than the website itself
     *
     * @return string
     */
    public function getFolderAppRoot() : string
    {
        return $this->folderAppRoot;
    }

    public function setFolderAppRoot($folder)
    {

        // Respect OS directory separator
        $folder=str_replace('/', DS, $folder);

        // Be sure that there is a slash at the end
        $folder=rtrim($folder, DS).DS;

        $this->folderAppRoot= $folder;
    }

    /**
     * Return the name of the folder (relative) of the documents folder
     *
     * @param  bool $absolute Return the full path (f.i. 'C:\Repository\notes\docs\') if True, the relative one (f.i. 'docs') if False
     *                         the relative one (f.i. 'docs') if False
     * @return string
     */
    public function getFolderDocs(bool $absolute = true) : string
    {
        return ($absolute?$this->getFolderWebRoot():'').$this->folderDocs;
    } // function getFolderDocs

    public function setFolderDocs($folder)
    {

        // Respect OS directory separator
        $folder=str_replace('/', DS, $folder);

        // Be sure that there is a slash at the end
        $folder=rtrim($folder, DS).DS;

        $this->folderDocs = $folder;
    } // function setFolderDocs

    /**
     * Return the root folder of the website (f.i. 'C:\Repository\notes\')
     *
     * @return string
     */
    public function getFolderWebRoot() : string
    {
        return $this->folderWebRoot;
    } // function getFolderWebRoot()

    public function setFolderWebRoot(string $folder)
    {
        $this->folderWebRoot=rtrim($folder, DS).DS;
    } // function setFolderWebRoot()

    public function getFolderLibs() : string
    {
        return $this->getFolderAppRoot().'libs'.DS;
    }

    public function getFolderTasks() : string
    {
        return $this->getFolderAppRoot().'classes'.DS.'tasks'.DS;
    }

    public function getFolderTemplates() : string
    {
        return $this->getFolderAppRoot().'templates'.DS;
    }

    /**
     * Return the template to use for the screen display or the html output.
     * Get the template info from the settings.json when the node 'templates' is found there
     *
     * @param  string $default Default filename with extension (f.i. 'screen' or 'html')
     * @return string           Full path to the file
     */
    public function getTemplateFile(string $default = 'screen') : string
    {

        $tmpl=$default;

        if (isset($this->json['templates'])) {
            if (isset($this->json['templates'][$default])) {
                $tmpl=$this->sanitizeFileName($this->json['templates'][$default]);
            }
        }

        if ($tmpl!=='') {
            if (!\AeSecure\Files::fileExists($fname = $this->getFolderTemplates().$tmpl.'.php')) {
                // The specified template doesn't exists. Back to the default one;
                if ($this->getDebugMode()) {
                    echo '<span style="font-size:0.8em;">Debug | '.__FILE__.'::'.__LINE__.'</span>&nbsp;-&nbsp;';
                }
                echo '<strong><em>Template ['.$fname.'] not found, please review your settings.json file.</em></strong>';
                die();
            }
        } else { // if ($tmpl!=='')

            $fname=$this->getFolderTemplates().$tmpl.'.php';
        } // if ($tmpl!=='')

        return $fname;
    } // function getTemplateFile()

    /**
     * Use browser's cache or not depending on settings.json
     *
     * @return bool
     */
    public function getOptimisationUseCache() : bool
    {
        $bReturn=false;

        if (isset($this->json['optimisation'])) {
            $tmp=$this->json['optimisation'];
            if (isset($tmp['cache'])) {
                $bReturn=(($tmp['cache']==1)?true:false);
            }
        }
        return $bReturn;
    } // function getOptimisationUseCache()

    /**
     * Use lazyload or not depending on settings.json
     *
     * @return bool
     */
    public function getOptimisationLazyLoad() : bool
    {
        $bReturn=false;

        if (isset($this->json['optimisation'])) {
            $tmp=$this->json['optimisation'];
            if (isset($tmp['lazyload'])) {
                $bReturn=(($tmp['lazyload']==1)?true:false);
            }
        }

        return $bReturn;
    } // function getOptimisationLazyLoad()

    /**
     * Return the Google font to use (from settings.json)
     *
     * @param  bool $css If true, return a css block for using the font(s).  If false, return the font(s) name
     * @return string
     */
    public function getPageGoogleFont(bool $css = true) : string
    {

        $return='';

        if (isset($this->json['page'])) {
            if (isset($this->json['page']['google_font'])) {
                $font=str_replace(' ', '+', $this->json['page']['google_font']);

                if ($css===true) {
                    if ($font!=='') {
                        $result='<link href="https://fonts.googleapis.com/css?family='.$font.'" rel="stylesheet">';

                        $i=0;
                        $return='<style>';
                        $sFontName=str_replace('+', ' ', $font);
                        for ($i=1; $i<7; $i++) {
                            $return.='page h'.$i.'{font-family:"'.$sFontName.'";}';
                        }
                        $return.='</style>';
                    } // if ($font!=='')
                } else { // if ($css===true)
                    $return = $font;
                } // if ($css===true)
            }
        } // if (isset($this->json['page']))

        return $return;
    } // function getPageGoogleFont()

    /**
     * Return the max width size for images (from settings.json)
     *
     * @return string
     */
    public function getPageImgMaxWidth() : string
    {

        $return=IMG_MAX_WIDTH;

        if (isset($this->json['page'])) {
            if (isset($this->json['page']['img_maxwidth'])) {
                $return=trim($this->json['page']['img_maxwidth']);
            }
        }

        return $return;
    } // function getPageImgMaxWidth()

    /**
     * Return the value for the robots info in the header
     *
     * @return string
     */
    public function getPageRobots() : string
    {

        $return='index, follow';

        if (isset($this->json['page'])) {
            if (isset($this->json['page']['robots'])) {
                $return=trim($this->json['page']['robots']);
            }
        }

        return $return;
    } // function getPageRobots()

    /**
     * Return the password used for encryptions
     *
     * @return string
     */
    public function getSiteName() : string
    {

        $sReturn='';

        if (isset($this->json['site_name'])) {
            $sReturn=trim($this->json['site_name']);
        }

        return $sReturn;
    } // function getSiteName()

    /**
     * Max allowed size for the search string
     *
     * @return int
     */
    public function getSearchMaxLength() : int
    {
        return SEARCH_MAX_LENGTH;
    }

    /**
     * Tags to automatically select when displaying the page
     *
     * @return string
     */
    public function getTagsAutoSelect() : string
    {

        if (isset($this->json['tags'])) {
            return implode($this->json['tags'], ",");
        } else {
            return '';
        }
    } // function getTagsAutoSelect()

    /**
     * Prefix to use to indicate a word as a tag
     *
     * @return string
     */
    public function getPrefixTag() : string
    {
        return PREFIX_TAG;
    } // function getPrefixTag()

    /**
     * Should nodes of the treeview be opened at loading time ?
     *
     * @return bool
     */
    public function getTreeOpened() : bool
    {

        $bReturn=false;

        if (isset($this->json['list'])) {
            if (isset($this->json['list']['opened'])) {
                $bReturn=($this->json['list']['opened']==1?true:false);
            }
        }

        return $bReturn ? true : false;

    } // function getTreeOpened()

    /**
     * List of folders that should be immediately opened
     *
     * @return array
     */
    public function getTreeFoldersAutoOpen() : array
    {

        $arr=array();

        if (isset($this->json['list'])) {
            if (isset($this->json['list']['auto_open'])) {
                foreach ($this->json['list']['auto_open'] as $folder) {
                    // Respect OS directory separator
                    $folder=rtrim(str_replace('/', DS, $folder), DS);
                    // List of folders that should be immediatly opened
                    $arr[]=$this->getFolderDocs(true).$folder;
                }
            }
        } // if(isset($this->json['list']))

        return $arr;
    } // function getTreeFoldersAutoOpen()

    /**
     * Return the password used for encryptions
     *
     * @return string
     */
    public function getEncryptionPassword() : string
    {

        $sReturn='';

        if (isset($this->json['encryption'])) {
            if (isset($this->json['encryption']['password'])) {
                $sReturn=trim($this->json['encryption']['password']);
            }
        }

        return $sReturn;
    } // function getEncryptionPassword()

    /**
     * Return the method used for encryptions
     *
     * @return string
     */
    public function getEncryptionMethod() : string
    {

        $sReturn='aes-256-ctr';

        if (isset($this->json['encryption'])) {
            if (isset($this->json['encryption']['method'])) {
                $sReturn=trim($this->json['encryption']['method']);
                if ($sReturn==='') {
                    $sReturn='aes-256-ctr';
                }
            }
        }

        return $sReturn;
    } // function getEncryptionMethod()

    /**
     * Allow editions ?
     *
     * @return bool
     */
    public function getEditAllowed() : bool
    {

        $bReturn=EDITOR;

        if (isset($this->json['editor'])) {
            $bReturn=($this->json['editor']==1?true:false);
        }

        return $bReturn;
    } // function getEditAllowed()

    public function getchmod(string $type = 'folder') : int
    {
        return ($type==='folder' ? CHMOD_FOLDER : CHMOD_FILE);
    } // function getchmod()

    /**
     * Can we use the navigator localStorage cache system ?
     *
     * @return bool
     */
    public function getUseLocalCache() : bool
    {
        $bReturn=true;

        if (isset($this->json['optimisation'])) {
            $tmp=$this->json['optimisation'];
            if (isset($tmp['localStorage'])) {
                $bReturn=(($tmp['localStorage']==1)?true:false);
            }
        }
        return $bReturn;
    }
} // class Settings
