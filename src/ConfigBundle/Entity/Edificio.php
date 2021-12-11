<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ConfigBundle\Entity\Edificio
 * @ORM\Table(name="edificio",uniqueConstraints={@ORM\UniqueConstraint(name="edificio_idx", columns={"ubicacion_id", "nombre"})})
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\UbicacionRepository")
 * @UniqueEntity(
 *     fields={"ubicacion", "nombre"}, errorPath="nombre",
 *     message="Este Edificio ya existe en esta Ubicación."
 * )
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Edificio {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Ubicacion", inversedBy="edificios")
     * @ORM\JoinColumn(name="ubicacion_id", referencedColumnName="id")
     */
    protected $ubicacion;

    /**
     * @ORM\OneToMany(targetEntity="ConfigBundle\Entity\Departamento", mappedBy="edificio", cascade={"remove"})
     */
    protected $departamentos;

    /**
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\Usuario", mappedBy="edificios")
     */
    private $usuarios;

    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __toString() {
        return $this->nombre;
    }

    public function textoCompleto() {
        return $this->getUbicacion() . ' - ' . $this->nombre;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->departamentos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Edificio
     */
    public function setNombre($nombre) {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Edificio
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Edificio
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Edificio
     */
    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

    /**
     * Set ubicacion
     *
     * @param \ConfigBundle\Entity\Ubicacion $ubicacion
     * @return Edificio
     */
    public function setUbicacion(\ConfigBundle\Entity\Ubicacion $ubicacion = null) {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    /**
     * Get ubicacion
     *
     * @return \ConfigBundle\Entity\Ubicacion
     */
    public function getUbicacion() {
        return $this->ubicacion;
    }

    /**
     * Add departamentos
     *
     * @param \ConfigBundle\Entity\Departamento $departamentos
     * @return Edificio
     */
    public function addDepartamento(\ConfigBundle\Entity\Departamento $departamentos) {
        $this->departamentos[] = $departamentos;

        return $this;
    }

    /**
     * Remove departamentos
     *
     * @param \ConfigBundle\Entity\Departamento $departamentos
     */
    public function removeDepartamento(\ConfigBundle\Entity\Departamento $departamentos) {
        $this->departamentos->removeElement($departamentos);
    }

    /**
     * Get departamentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDepartamentos() {
        return $this->departamentos;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Edificio
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return Edificio
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }


    /**
     * Add usuarios
     *
     * @param \ConfigBundle\Entity\Usuario $usuarios
     * @return Edificio
     */
    public function addUsuario(\ConfigBundle\Entity\Usuario $usuarios)
    {
        $this->usuarios[] = $usuarios;

        return $this;
    }

    /**
     * Remove usuarios
     *
     * @param \ConfigBundle\Entity\Usuario $usuarios
     */
    public function removeUsuario(\ConfigBundle\Entity\Usuario $usuarios)
    {
        $this->usuarios->removeElement($usuarios);
    }

    /**
     * Get usuarios
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsuarios()
    {
        return $this->usuarios;
    }
}
