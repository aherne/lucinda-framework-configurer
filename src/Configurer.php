<?php
namespace Lucinda\Configurer;

require_once("FeaturesSelection.php");
require_once("xml/StdoutXMLInstallation.php");
require_once("xml/StderrXMLInstallation.php");
require_once("CodeInstallation.php");
require_once("SQLInstallation.php");

class Configurer
{
    private $features;

    /**
     * @param string $installationFolder
     */
    public function __construct($installationFolder)
    {
        $this->features = $this->getSelectedFeatures();
        $this->writeFiles($installationFolder);
    }

    /**
     * Gets user selected install features.
     *
     * @return Features
     */
    private function getSelectedFeatures()
    {
        $selection = new FeaturesSelection();
        return $selection->getChoices();
    }

    /**
     * Creates project on disk based on features selected by user
     * 
     * @param string $installationFolder
     */
    private function writeFiles($installationFolder)
    {
        chmod($installationFolder, 0777);
        chdir($installationFolder);
        
        echo "Setting up XML files\n";
        new StdoutXMLInstallation($this->features, $installationFolder.DIRECTORY_SEPARATOR."stdout.xml");
        new StderrXMLInstallation($this->features, $installationFolder.DIRECTORY_SEPARATOR."stderr.xml");
        
        echo "Setting up php dependencies\n";
        new CodeInstallation($this->features, $installationFolder);
        
        echo "Setting up tables\n";
        new SQLInstallation($this->features);
    }
}
