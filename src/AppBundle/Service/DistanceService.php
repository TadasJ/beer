<?php
namespace AppBundle\Service;
use AppBundle\Entity\Geocode;
use Doctrine\ORM\EntityManager;

/**
 * Class HaversineService
 */
class DistanceService
{

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Returns distance (km) between two pairs of lat/long coordinates
     * @param $latFrom
     * @param $longFrom
     * @param $latTo
     * @param $longTo
     * @return int
     */
    public function getHaversineDistance($latFrom, $longFrom, $latTo, $longTo)
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($longFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($longTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }

    /**
     * Returns all points roughly within distance (km) of given coordinates
     * @param $lat
     * @param $long
     * @param $distance
     * @return Geocode[]|array
     */
    public function getPointsWithinDistance($lat, $long, $distance)
    {
        $angleRadius = $distance / 111; // a degree is ~111km
        $minLat = $lat - $angleRadius;
        $maxLat = $lat + $angleRadius;
        $minLong = $long - $angleRadius;
        $maxLong = $long + $angleRadius;

        $qb = $this->em->getRepository('AppBundle:Geocode')->createQueryBuilder('g');
        $qb->select('g')
            ->where('g.latitude BETWEEN :minLat AND :maxLat')
            ->andWhere('g.longitude BETWEEN :minLong AND :maxLong')
            ->setParameter('minLat', $minLat)
            ->setParameter('maxLat', $maxLat)
            ->setParameter('minLong', $minLong)
            ->setParameter('maxLong', $maxLong);

        return $qb->getQuery()->getResult();
    }
}
