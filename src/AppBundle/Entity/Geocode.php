<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Geocode
 *
 * @ORM\Table(name="geocode")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GeocodeRepository")
 */
class Geocode
{
    const IMPORT_FILE_NAME = "geocodes.csv";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float")
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float")
     */
    private $longitude;

    /**
     * @ORM\OneToOne(targetEntity="Brewery", inversedBy="geocode")
     * @ORM\JoinColumn(name="brewery_id", referencedColumnName="id")
     */
    private $brewery;

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
     * Set latitude
     *
     * @param float $latitude
     * @return Geocode
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return Geocode
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @return Brewery
     */
    public function getBrewery()
    {
        return $this->brewery;
    }

    /**
     * @param Brewery $brewery
     */
    public function setBrewery(Brewery $brewery)
    {
        $this->brewery = $brewery;
    }
}
