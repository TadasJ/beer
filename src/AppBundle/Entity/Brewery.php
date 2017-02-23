<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Brewery
 *
 * @ORM\Table(name="brewery")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BreweryRepository")
 */
class Brewery
{
    const IMPORT_FILE_NAME = "breweries.csv";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="import_id", type="integer")
     */
    private $importId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity="Geocode", mappedBy="brewery")
     */
    private $geocode;

    /**
     * @ORM\OneToMany(targetEntity="Beer", mappedBy="brewery")
     */
    private $beers;

    public function __construct() {
        $this->beers = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * @param int $importId
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Brewery
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Geocode
     */
    public function getGeocode()
    {
        return $this->geocode;
    }

    /**
     * @param Geocode $geocode
     */
    public function setGeocode(Geocode $geocode)
    {
        $this->geocode = $geocode;
    }

    /**
     * @return Beer[]|ArrayCollection
     */
    public function getBeers()
    {
        return $this->beers;
    }

    /**
     * @param Beer $beer
     */
    public function addBeer(Beer $beer)
    {
        $this->beers->add($beer);
    }

    /**
     * @param Beer $beer
     */
    public function removeBeer(Beer $beer)
    {
        if($this->beers->contains($beer)) {
            $this->beers->removeElement($beer);
        }
    }
}
