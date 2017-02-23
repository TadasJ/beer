<?php

namespace AppBundle\Command;

use AppBundle\Entity\Beer;
use AppBundle\Entity\Brewery;
use AppBundle\Entity\Geocode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportDataCommand extends ContainerAwareCommand
{
    const ENTITY_BREWERY = "brewery";
    const ENTITY_BEER = "beer";
    const ENTITY_GEOCODE = "geocode";

    protected function configure()
    {
        $this
            ->setName('app:import-data')
            ->setDescription('Imports all beer data')
            ->setHelp("This command imports beer data from files located in Resources/importData.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Importing breweries...");
        $this->importFromCsv(Brewery::IMPORT_FILE_NAME, self::ENTITY_BREWERY);
        $output->writeln("Importing beers...");
        $this->importFromCsv(Beer::IMPORT_FILE_NAME, self::ENTITY_BEER);
        $output->writeln("Importing geocodes...");
        $this->importFromCsv(Geocode::IMPORT_FILE_NAME, self::ENTITY_GEOCODE);
        $output->writeln("Import complete. Cheers!");
    }

    private function importFromCsv($filename, $entityType)
    {
        $filePath = $this->get('data_path_service')->getFilePath($filename);
        $openFile = fopen($filePath, "r");

        if($openFile === FALSE) {
            echo "Error: Could not open ". $filePath;
            return;
        }

        $em = $this->get('doctrine')->getManager();
        $head = fgetcsv($openFile, null, ",");

        $breweriesWithGeocodes = [];

        while (($data = fgetcsv($openFile, null, ",")) !== FALSE) {
            $row = array_combine($head, $data);
            switch($entityType) {
                case self::ENTITY_BREWERY:
                    $this->handleBreweryImport($row);
                    break;
                case self::ENTITY_BEER:
                    $this->handleBeerImport($row);
                    break;
                case self::ENTITY_GEOCODE:
                    if(!in_array($row['brewery_id'], $breweriesWithGeocodes)) {
                        $this->handleGeocodeImport($row);
                        $breweriesWithGeocodes[] = $row['brewery_id'];
                    }
                    break;
                default:
                    echo "Error: Uknown entity type ".$entityType;
                    break;
            }
        }

        fclose($openFile);
        $em->flush();
    }

    private function handleBreweryImport($row)
    {
        $em = $this->get('doctrine')->getManager();
        $brewery = $em->getRepository('AppBundle:Brewery')->findOneByName($row['name']);

        //brewery already exists, skip.
        if($brewery) {
            return;
        }

        $brewery = new Brewery();
        $brewery->setImportId($row['id']);
        $brewery->setName($row['name']);
        $em->persist($brewery);
    }

    private function handleBeerImport($row)
    {
        $em = $this->get('doctrine')->getManager();
        $brewery = $em->getRepository('AppBundle:Brewery')->findOneByImportId($row['brewery_id']);
        $beer = $em->getRepository('AppBundle:Beer')->findOneBy(['name' => $row['name'], 'brewery' => $brewery]);

        //beer already exists, or brewery doesn't skip.
        if($beer || !$brewery) {
            return;
        }

        $beer = new Beer();
        $beer->setName($row['name']);
        $beer->setBrewery($brewery);
        $em->persist($beer);
    }

    private function handleGeocodeImport($row)
    {
        $em = $this->get('doctrine')->getManager();
        $brewery = $em->getRepository('AppBundle:Brewery')->findOneByImportId($row['brewery_id']);
        $geocode = $em->getRepository('AppBundle:Geocode')->findOneByBrewery($brewery);

        //geocode already exists or brewery doesn't, skip.
        if($geocode || !$brewery) {
            return;
        }

        $geocode = new Geocode();
        $geocode->setLatitude($row['latitude']);
        $geocode->setLongitude($row['longitude']);
        $geocode->setBrewery($brewery);
        $em->persist($geocode);
    }

    private function get($name)
    {
        return $this->getContainer()->get($name);
    }
}