<?php

namespace AlanKent\ClusterControl\Commands;

use AlanKent\ClusterControl\ClusterControl;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClusterPrepareCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cc:clusterprepare')
            ->setDescription(
                'Read the specified cluster and write the config file with the current cluster members.'
            )->addOption(
                'conf',
                null,
                InputOption::VALUE_REQUIRED,
                'Configuration file',
                ClusterControl::DEFAULT_CONFIG_FILE
            )->addArgument(
                'cluster',
                InputArgument::REQUIRED,
                'Cluster name to refresh.'
            );
    }

    /**
     * Work out the members of the cluster and write that list to the configuration file.
     * Writes to stdout the wait index to pass to cc:clusterwatch to spot changes.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int Returns 0 on success.
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $conf = $input->getOption('conf');
        $cluster = $input->getArgument('cluster');
        $debug = $output->isVerbose();

        $clusterControl = new ClusterControl($conf, $debug);

        $resp = $clusterControl->readClusterMembers($cluster, null);
        $waitIndex = $resp['index'];
        $clusterMembers = $resp['members'];
        $clusterControl->writeClusterConfig($cluster, $clusterMembers);
        $output->write($waitIndex, true);
        return 0; // Success
    }
}
