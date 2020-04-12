<?php
namespace Lucinda\Configurer;

use Lucinda\Configurer\Features\Features;
use Lucinda\Configurer\Features\FeaturesSelector;
use Lucinda\Configurer\XML\StdoutInstaller;
use Lucinda\Configurer\XML\StderrInstaller;
use Lucinda\Configurer\Code\Installer as CodeInstaller;
use Lucinda\Configurer\SQL\Installer as SqlInstaller;

class ProjectCreator
{
    /**
     * @param string $installationFolder
     */
    public function __construct(string $installationFolder)
    {
        $features = $this->getSelectedFeatures();
        $this->writeFiles($installationFolder, $features);
    }

    /**
     * Gets user selected install features and validates them in the process
     *
     * @return Features
     * @throws \Exception If process fails
     */
    private function getSelectedFeatures(): Features
    {
        $selection = new FeaturesSelector();
        $features = $selection->getChoices();
        new FeaturesValidator($features);
        return $features;
    }

    /**
     * Creates project on disk based on features selected by user
     *
     * @param string $installationFolder
     */
    private function writeFiles(string $installationFolder, Features $features)
    {
        chmod($installationFolder, 0777);
        chdir($installationFolder);
        
        echo "Setting up XML files\n";
        new StdoutInstaller($features, $installationFolder.DIRECTORY_SEPARATOR."stdout.xml");
        new StderrInstaller($features, $installationFolder.DIRECTORY_SEPARATOR."stderr.xml");
        
        echo "Setting up php dependencies\n";
        new CodeInstaller($features, $installationFolder);
        
        echo "Setting up tables\n";
        new SqlInstaller($features);
    }
}
