<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Usuario
 * @ORM\Table(name="usuario",uniqueConstraints={@ORM\UniqueConstraint(name="username_idx", columns={"username"})})
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\UsuarioRepository")
 * @UniqueEntity(
 *     fields={"username"}, errorPath="username", message="Este nombre de usuario ya existe."
 * )
 */
class Usuario implements UserInterface {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $username
     * @ORM\Column(name="username", type="string",unique=true)
     * @Assert\NotBlank()
     */
    protected $username;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string")
     * @Assert\NotBlank()
     */
    protected $nombre;

    /**
     * @var string $dni
     * @ORM\Column(name="dni", type="string", length=8, nullable=true)
     *
     */
    protected $dni;

    /**
     * @var string $email
     * @ORM\Column(name="email", type="string")
     * @Assert\NotBlank()
     */
    protected $email;

    /**
     * @var string $password
     * @ORM\Column(name="password", type="string")
     */
    protected $password;

    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true;

    /**
     * @ORM\Column(name="fecha_alta", type="datetime")
     */
    protected $fechaAlta;

    /**
     * @ORM\Column(name="ultima_conexion", type="datetime", nullable=true)
     */
    protected $ultimaConexion;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Rol")
     * @ORM\JoinColumn(name="rol_id", referencedColumnName="id",onDelete="SET NULL")
     */
    protected $rol;
    protected $roles;

    /**
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\Edificio", inversedBy="usuarios")
     * @ORM\JoinTable(name="edificios_x_usuario",
     *      joinColumns={@ORM\JoinColumn(name="usuario_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="edificio_id", referencedColumnName="id")}
     * )
     */
    private $edificios;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrdenTrabajo", mappedBy="tecnico")
     */
    protected $ordenesTrabajo;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Mensajeria", mappedBy="destinatario")
     */
    protected $mensajes;

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
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    public function __toString() {
        return ($this->nombre) ? $this->nombre : ' ';
    }

    public function getTitle() {
        return '" ' . $this->username . ' - ' . $this->nombre . ' "';
    }

    public function getInitRoute() {
        return $this->getRol()->getInitRoute();
    }

    public function getAccess($slug) {
        return $this->rol->getAccess($slug);
    }

    public function __construct() {
        $this->fechaAlta = new \DateTime();
    }

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username) {
        $this->username = strtoupper($username);
    }

    /**
     * Get username
     * @return string
     */
    public function getUsername() {
        return strtoupper($this->username);
    }

    /**
     * Set nombre
     * @param string $nombre
     */
    public function setNombre($nombre) {
        $this->nombre = $nombre;
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
     * Set dni
     *
     * @param string $dni
     */
    public function setDni($dni) {
        $this->dni = $dni;
    }

    /**
     * Get dni
     *
     * @return string
     */
    public function getDni() {
        return $this->dni;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
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
     *  password
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set activo
     * @param boolean $activo
     * @return Usuario
     */
    public function setActivo($activo) {
        $this->activo = $activo;
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean
     */
    public function getActivo() {
        return $this->activo;
    }

    /**
     * Set fechaAlta
     *
     * @param \DateTime $fechaAlta
     * @return Usuario
     */
    public function setFechaAlta($fechaAlta) {
        $this->fechaAlta = $fechaAlta;

        return $this;
    }

    /**
     * Get fechaAlta
     *
     * @return \DateTime
     */
    public function getFechaAlta() {
        return $this->fechaAlta;
    }

    // IMPLEMENTACION DE USERINTERFACE
    public function getRoles() {
        $datos[] = $this->rol->getNombre();
        return $datos;
    }

    public function getSalt() {
        return false;
    }

    public function eraseCredentials() {
        return false;
    }

    public function equals(UserInterface $user) {
        return $this->getUsername() == $user->getUsername();
    }

    /**
     * Set rol
     *
     * @param \ConfigBundle\Entity\Rol $rol
     * @return Usuario
     */
    public function setRol(\ConfigBundle\Entity\Rol $rol = null) {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return \ConfigBundle\Entity\Rol
     */
    public function getRol() {
        return $this->rol;
    }

    /**
     * Set ultimaConexion
     *
     * @param \DateTime $ultimaConexion
     * @return Usuario
     */
    public function setUltimaConexion($ultimaConexion) {
        $this->ultimaConexion = $ultimaConexion;

        return $this;
    }

    /**
     * Get ultimaConexion
     *
     * @return \DateTime
     */
    public function getUltimaConexion() {
        return $this->ultimaConexion;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Usuario
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
     * @return Usuario
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Usuario
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
     * @return Usuario
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
     * Add ordenesTrabajo
     *
     * @param \AppBundle\Entity\OrdenTrabajo $ordenesTrabajo
     * @return Usuario
     */
    public function addOrdenesTrabajo(\AppBundle\Entity\OrdenTrabajo $ordenesTrabajo) {
        $this->ordenesTrabajo[] = $ordenesTrabajo;

        return $this;
    }

    /**
     * Remove ordenesTrabajo
     *
     * @param \AppBundle\Entity\OrdenTrabajo $ordenesTrabajo
     */
    public function removeOrdenesTrabajo(\AppBundle\Entity\OrdenTrabajo $ordenesTrabajo) {
        $this->ordenesTrabajo->removeElement($ordenesTrabajo);
    }

    /**
     * Get ordenesTrabajo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrdenesTrabajo() {
        return $this->ordenesTrabajo;
    }

    public function getOrdenesAbiertas() {
        $cant = 0;
        foreach ($this->ordenesTrabajo as $orden) {
            if ($orden->getEstado() == 'ABIERTO') {
                $cant = $cant + 1;
            }
        }
        return $cant;
    }

    public function getOrdenesAbiertasTablero() {
        $ot = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->ordenesTrabajo as $orden) {
            if ($orden->getEstado() == 'ABIERTO') {
                $ot->add($orden);
            }
        }
        return $ot;
    }

    /**
     * Add mensajes
     *
     * @param \AppBundle\Entity\Mensajeria $mensajes
     * @return Usuario
     */
    public function addMensaje(\AppBundle\Entity\Mensajeria $mensajes) {
        $this->mensajes[] = $mensajes;

        return $this;
    }

    /**
     * Remove mensajes
     *
     * @param \AppBundle\Entity\Mensajeria $mensajes
     */
    public function removeMensaje(\AppBundle\Entity\Mensajeria $mensajes) {
        $this->mensajes->removeElement($mensajes);
    }

    /**
     * Get mensajes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMensajes() {
        return $this->mensajes;
    }

    public function getCantidadMensajesNoLeidos() {
        $cant = 0;
        foreach ($this->mensajes as $msj) {
            if (is_null($msj->getFechaLeido())) {
                $cant = $cant + 1;
            }
        }
        return $cant;
    }

    /**
     * Add edificios
     *
     * @param \ConfigBundle\Entity\Edificio $edificios
     * @return Usuario
     */
    public function addEdificio(\ConfigBundle\Entity\Edificio $edificios) {
        $this->edificios[] = $edificios;

        return $this;
    }

    /**
     * Remove edificios
     *
     * @param \ConfigBundle\Entity\Edificio $edificios
     */
    public function removeEdificio(\ConfigBundle\Entity\Edificio $edificios) {
        $this->edificios->removeElement($edificios);
    }

    /**
     * Get edificios
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEdificios() {
        return $this->edificios;
    }

    /*
     * Ubicaciones permitidas en base a los edificios
     */

    public function getUbicacionesPermitidas() {
        $ubic = array();
        foreach ($this->getEdificios() as $edif) {
            $ubic[] = $edif->getUbicacion()->getId();
        }
        return array_values(array_unique($ubic));
    }

}