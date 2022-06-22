<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * ConfigBundle\Entity\Departamento
 * @ORM\Table(name="departamento",uniqueConstraints={@ORM\UniqueConstraint(name="departamento_idx", columns={"edificio_id", "nombre"})})
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\UbicacionRepository")
 * @UniqueEntity(
 *     fields={"edificio","nombre"}, errorPath="nombre",
 *     message="Este Departamento ya existe en este Edificio."
 * )
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Departamento {
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
     * @var string $direccion
     * @ORM\Column(name="direccion", type="string", nullable=true)
     */
    protected $direccion;

    /**
     * @var string $telefono
     * @ORM\Column(name="telefono", type="string", nullable=true)
     */
    protected $telefono;

    /**
     * @var string $responsable
     * @ORM\Column(name="responsable", type="string", nullable=true)
     */
    protected $responsable;

    /**
     * @var string $email
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

    /**
     * @ORM\Column(name="deposito", type="boolean")
     */
    protected $deposito = false;

    /**
     * @ORM\Column(name="servicio_tecnico", type="boolean")
     */
    protected $servicioTecnico = false;

    /**
     * @ORM\Column(name="inicial", type="boolean", nullable=true)
     */
    protected $inicial = false;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Localidad")
     * @ORM\JoinColumn(name="localidad_id", referencedColumnName="id")
     */
    protected $localidad;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Edificio", inversedBy="departamentos")
     * @ORM\JoinColumn(name="edificio_id", referencedColumnName="id")
     */
    protected $edificio;

    /**
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\Piso",inversedBy="departamentos")
     * @ORM\JoinTable(name="pisos_x_departamento",
     *      joinColumns={@ORM\JoinColumn(name="departamento_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="piso_id", referencedColumnName="id")}
     * )
     */
    private $pisos;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\EquipoUbicacion", mappedBy="departamento")
     */
    protected $equipos;

    /**
     * @var string $ipPrincipal
     * @ORM\Column(name="ip_principal", type="string", nullable=true)
     */
    protected $ipPrincipal;

    /**
     * @var string $ipRespaldo
     * @ORM\Column(name="ip_respaldo", type="string", nullable=true)
     */
    protected $ipRespaldo;

    /**
     * @ORM\OneToOne(targetEntity="ConfigBundle\Entity\DepartamentoProveedor", mappedBy="departamento",cascade={"persist"})
     */
    protected $proveedor;

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

    public function getNombreCompleto() {
        return ($this->getEdificio()) ?
                $this->getEdificio()->getUbicacion()->getAbreviatura() . ' - ' . $this->getEdificio()->getNombre() . ' - ' . $this->getNombre() : '';
    }

    public function getEdificioDepartamento() {
        return $this->getEdificio()->getNombre() . ' - ' . $this->getNombre();
    }

    public function getCantidadEquipos() {
        $cant = 0;
        foreach ($this->getEquipos() as $equipo) {
            if ($equipo->getActual()) {
                $cant += 1;
            }
        }
        return $cant;
    }

    public function getCantidadEquiposParaMonitoreo() {
        $cant = 0;
        foreach ($this->getEquipos() as $equipo) {
            if ($equipo->getActual() && $equipo->getRedIp()) {
                $cant += 1;
            }
        }
        return $cant;
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
     * @return Departamento
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
     * Set direccion
     *
     * @param string $direccion
     * @return Departamento
     */
    public function setDireccion($direccion) {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get direccion
     *
     * @return string
     */
    public function getDireccion() {
        return $this->direccion;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Departamento
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
     * @return Departamento
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
     * @return Departamento
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
     * Set localidad
     *
     * @param \ConfigBundle\Entity\Localidad $localidad
     * @return Departamento
     */
    public function setLocalidad(\ConfigBundle\Entity\Localidad $localidad = null) {
        $this->localidad = $localidad;

        return $this;
    }

    /**
     * Get localidad
     *
     * @return \ConfigBundle\Entity\Localidad
     */
    public function getLocalidad() {
        return $this->localidad;
    }

    /**
     * Set edificio
     *
     * @param \ConfigBundle\Entity\Edificio $edificio
     * @return Departamento
     */
    public function setEdificio(\ConfigBundle\Entity\Edificio $edificio = null) {
        $this->edificio = $edificio;

        return $this;
    }

    /**
     * Get edificio
     *
     * @return \ConfigBundle\Entity\Edificio
     */
    public function getEdificio() {
        return $this->edificio;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Departamento
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
     * @return Departamento
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
     * Set deposito
     *
     * @param boolean $deposito
     * @return Departamento
     */
    public function setDeposito($deposito) {
        $this->deposito = $deposito;

        return $this;
    }

    /**
     * Get deposito
     *
     * @return boolean
     */
    public function getDeposito() {
        return $this->deposito;
    }

    /**
     * Set servicioTecnico
     *
     * @param boolean $servicioTecnico
     * @return Departamento
     */
    public function setServicioTecnico($servicioTecnico) {
        $this->servicioTecnico = $servicioTecnico;

        return $this;
    }

    /**
     * Get servicioTecnico
     *
     * @return boolean
     */
    public function getServicioTecnico() {
        return $this->servicioTecnico;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return Departamento
     */
    public function setTelefono($telefono) {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get telefono
     *
     * @return string
     */
    public function getTelefono() {
        return $this->telefono;
    }

    /**
     * Set responsable
     *
     * @param string $responsable
     * @return Departamento
     */
    public function setResponsable($responsable) {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return string
     */
    public function getResponsable() {
        return $this->responsable;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Departamento
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Departamento
     */
    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones() {
        return $this->observaciones;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->pisos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add pisos
     *
     * @param \ConfigBundle\Entity\Piso $pisos
     * @return Departamento
     */
    public function addPiso(\ConfigBundle\Entity\Piso $pisos) {
        $this->pisos[] = $pisos;

        return $this;
    }

    /**
     * Remove pisos
     *
     * @param \ConfigBundle\Entity\Piso $pisos
     */
    public function removePiso(\ConfigBundle\Entity\Piso $pisos) {
        $this->pisos->removeElement($pisos);
    }

    /**
     * Get pisos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPisos() {
        return $this->pisos;
    }

    /**
     * Add equipos
     *
     * @param \AppBundle\Entity\EquipoUbicacion $equipos
     * @return Departamento
     */
    public function addEquipo(\AppBundle\Entity\EquipoUbicacion $equipos) {
        $this->equipos[] = $equipos;

        return $this;
    }

    /**
     * Remove equipos
     *
     * @param \AppBundle\Entity\EquipoUbicacion $equipos
     */
    public function removeEquipo(\AppBundle\Entity\EquipoUbicacion $equipos) {
        $this->equipos->removeElement($equipos);
    }

    /**
     * Get equipos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipos() {
        return $this->equipos;
    }

    /**
     * Set inicial
     *
     * @param boolean $inicial
     * @return Departamento
     */
    public function setInicial($inicial) {
        $this->inicial = $inicial;

        return $this;
    }

    /**
     * Get inicial
     *
     * @return boolean
     */
    public function getInicial() {
        return $this->inicial;
    }

    /**
     * Set ipPrincipal
     *
     * @param string $ipPrincipal
     * @return Departamento
     */
    public function setIpPrincipal($ipPrincipal) {
        $this->ipPrincipal = $ipPrincipal;

        return $this;
    }

    /**
     * Get ipPrincipal
     *
     * @return string
     */
    public function getIpPrincipal() {
        return $this->ipPrincipal;
    }

    /**
     * Set ipRespaldo
     *
     * @param string $ipRespaldo
     * @return Departamento
     */
    public function setIpRespaldo($ipRespaldo) {
        $this->ipRespaldo = $ipRespaldo;

        return $this;
    }

    /**
     * Get ipRespaldo
     *
     * @return string
     */
    public function getIpRespaldo() {
        return $this->ipRespaldo;
    }

    /**
     * Set proveedor
     *
     * @param \ConfigBundle\Entity\DepartamentoProveedor $proveedor
     * @return Departamento
     */
    public function setProveedor(\ConfigBundle\Entity\DepartamentoProveedor $proveedor = null) {
        $proveedor->setDepartamento($this);
        $this->proveedor = $proveedor;
        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \ConfigBundle\Entity\DepartamentoProveedor
     */
    public function getProveedor() {
        return $this->proveedor;
    }

}