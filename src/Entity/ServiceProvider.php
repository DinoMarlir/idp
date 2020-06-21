<?php

namespace App\Entity;

use App\Validator\X509Certificate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class ServiceProvider {

    use IdTrait;
    use UuidTrait;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(max="128")
     */
    private $entityId;

    /**
     * @ORM\Column(type="string")
     * @ORM\OrderBy()
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Url()
     * @Serializer\Exclude()
     */
    private $acs;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @X509Certificate()
     * @Serializer\Exclude()
     */
    private $certificate;

    /**
     * @ORM\ManyToMany(targetEntity="ServiceAttribute", mappedBy="services")
     * @Serializer\Exclude()
     */
    private $attributes;

    public function __construct() {
        $this->uuid = Uuid::uuid4();
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getEntityId() {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     * @return ServiceProvider
     */
    public function setEntityId($entityId) {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ServiceProvider
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ServiceProvider
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getAcs() {
        return $this->acs;
    }

    /**
     * @param string $acs
     * @return ServiceProvider
     */
    public function setAcs($acs) {
        $this->acs = $acs;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string $url
     * @return ServiceProvider
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificate() {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     * @return ServiceProvider
     */
    public function setCertificate($certificate) {
        $this->certificate = $certificate;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getAttributes(): Collection {
        return $this->attributes;
    }
}