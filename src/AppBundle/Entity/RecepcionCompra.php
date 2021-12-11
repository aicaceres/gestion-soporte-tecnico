<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * AppBundle\Entity\RecepcionCompra
 * @ORM\Table(name="recepcion_compra")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CompraRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class RecepcionCompra {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $fechaRecepcion
     * @ORM\Column(name="fecha_recepcion", type="datetime", nullable=false)
     */
    private $fechaRecepcion;

    /**
     * @var string $nroFactura
     * @ORM\Column(name="nro_factura", type="string",length=20, nullable=true)
     */
    protected $nroFactura;

    /**
     * @var string $nroRemito
     * @ORM\Column(name="nro_remito", type="string", length=20,nullable=true)
     */
    protected $nroRemito;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compra", inversedBy="recepciones")
     * @ORM\JoinColumn(name="compra_id", referencedColumnName="id")
     */
    protected $compra;

    /**
     * @var string $observaciones
     * @ORM\Column(name="observaciones", type="string", nullable=true)
     */
    protected $observaciones;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RecepcionCompraDetalle", mappedBy="recepcion",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $detalles;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;

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

    /**
     * MANEJO DE ARCHIVO
     */

    /**
     * @ORM\Column(name="path", type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * Set path
     * @param string $path
     * @return paciente
     */
    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    /**
     * Get path
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    public function getAbsolutePath() {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath() {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir() {
        // la ruta absoluta del directorio donde se deben
        // guardar los archivos cargados
        return __DIR__ . '/../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir() {
        // se deshace del __DIR__ para no meter la pata
        // al mostrar el documento/imagen cargada en la vista.
        return 'uploads/docs';
    }

    /**
     * @Assert\File()
     */
    private $file;
    private $filenameForRemove;

    /**
     * Get file.
     * @return UploadedFile
     */
    public function getFile() {
        return $this->file;
    }

    private $temp;

    /**
     * Sets file.
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null) {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        }
        else {
            $this->path = 'initial';
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (null !== $this->getFile()) {
            // haz lo que quieras para generar un nombre único
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename . '.' . $this->getFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (null === $this->getFile()) {
            return;
        }
        // si hay un error al mover el archivo, move() automáticamente
        // envía una excepción. This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);
        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            if (file_exists($this->getUploadRootDir() . '/' . $this->temp))
                unlink($this->getUploadRootDir() . '/' . $this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove() {
        $this->filenameForRemove = $this->getAbsolutePath();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload() {
        if ($this->filenameForRemove) {
            unlink($this->filenameForRemove);
        }
    }

    /*
     * FIN MANEJO DE ARCHIVO
     */

    /**
     * Constructor
     */
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set fechaRecepcion
     *
     * @param \DateTime $fechaRecepcion
     * @return RecepcionCompra
     */
    public function setFechaRecepcion($fechaRecepcion) {
        $this->fechaRecepcion = $fechaRecepcion;

        return $this;
    }

    /**
     * Get fechaRecepcion
     *
     * @return \DateTime
     */
    public function getFechaRecepcion() {
        return $this->fechaRecepcion;
    }

    /**
     * Set nroFactura
     *
     * @param string $nroFactura
     * @return RecepcionCompra
     */
    public function setNroFactura($nroFactura) {
        $this->nroFactura = $nroFactura;

        return $this;
    }

    /**
     * Get nroFactura
     *
     * @return string
     */
    public function getNroFactura() {
        return $this->nroFactura;
    }

    /**
     * Set nroRemito
     *
     * @param string $nroRemito
     * @return RecepcionCompra
     */
    public function setNroRemito($nroRemito) {
        $this->nroRemito = $nroRemito;

        return $this;
    }

    /**
     * Get nroRemito
     *
     * @return string
     */
    public function getNroRemito() {
        return $this->nroRemito;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return RecepcionCompra
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
     * Set created
     *
     * @param \DateTime $created
     * @return RecepcionCompra
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
     * @return RecepcionCompra
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
     * @return RecepcionCompra
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
     * Set compra
     *
     * @param \AppBundle\Entity\Compra $compra
     * @return RecepcionCompra
     */
    public function setCompra(\AppBundle\Entity\Compra $compra = null) {
        $this->compra = $compra;
        return $this;
    }

    /**
     * Get compra
     *
     * @return \AppBundle\Entity\Compra
     */
    public function getCompra() {
        return $this->compra;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $detalles
     * @return RecepcionCompra
     */
    public function addDetalle(\AppBundle\Entity\RecepcionCompraDetalle $detalles) {
        $detalles->setRecepcion($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\RecepcionCompraDetalle $detalles) {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalles() {
        return $this->detalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return RecepcionCompra
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
     * @return RecepcionCompra
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
     * @param \ConfigBundle\Entity\Departamento $deposito
     * @return RecepcionCompra
     */
    public function setDeposito(\ConfigBundle\Entity\Departamento $deposito = null) {
        $this->deposito = $deposito;

        return $this;
    }

    /**
     * Get deposito
     *
     * @return \ConfigBundle\Entity\Departamento
     */
    public function getDeposito() {
        return $this->deposito;
    }

}