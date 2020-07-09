<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SesNvcRepository")
 * @ORM\Table(name="ses_nvc", options={"collate"="utf8_general_ci"})
 */
class SesNvc
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $collection;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $key;

    /**
     * @ORM\Column(type="integer")
     */
    private $valueInteger;

    /**
     * @ORM\Column(type="string")
     */
    private $valueString;
}
