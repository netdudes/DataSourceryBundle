<?php

namespace Netdudes\DataSourceryBundle\Command;

use Netdudes\DataSourceryBundle\UQL\Autocomplete\Autocomplete;
use Netdudes\DataSourceryBundle\UQL\Autocomplete\PredictionEngine;
use Netdudes\DataSourceryBundle\UQL\Parser;
use Netdudes\U2\ApmBundle\Table\DataSource\TransactionDataSourceConfiguration;
use Netdudes\U2\CoreBundle\Import\ConfigurationCollector;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestAutocompleteCommand extends ContainerAwareCommand
{

    /**
     * @var ConfigurationCollector
     */
    protected $configurationCollector = null;

    protected function configure()
    {
        $this
            ->setName('sourcery:autocomplete')
            ->setDescription('Test autocomplete');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<fg=red>Write UQL to see autocompletion options </fg=red>\n");

        $autocomplete = $this->getContainer()->get('netdudes_data_sourcery.uql.autocomplete.factory')
            ->create($this->getContainer()->get('netdudes_u2_apm.transaction.data_source.configuration'));

        if ($input->isInteractive()) {
            $ast = null;
            while (1) {
                $output->write("\n> ");
                $line = trim(fgets(STDIN, 4096), "\n");
                if (feof(STDIN)) {
                    break;
                }
                if (false === $line) {
                    throw new \RuntimeException('Aborted');
                }
                $options = ['ERROR'];
                try {
                    $options = $autocomplete->autocomplete($line);
                } catch (\Exception $e) {
                    $output->writeln("<bg=red;fg=white>" . $e->getMessage() . "</bg=red;fg=white>");
                }

                foreach ($options as $option) {
                    $output->writeln($option);
                }
            }
        }
    }
}
