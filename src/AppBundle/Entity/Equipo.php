<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ConfigBundle\Controller\UtilsController;

/**
 * AppBundle\Entity\Equipo
 * @ORM\Table(name="equipo",uniqueConstraints={@ORM\UniqueConstraint(name="equipo_idx", columns={"tipo_id","nro_serie","nombre"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EquipoRepository")
 * @UniqueEntity(
 *     fields={"tipo","nroSerie","nombre"}, errorPath="nombre",
 *     message="Ya existe un equipo con la misma descripción o nro de serie para este tipo."
 * )
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
class Equipo {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string",unique=true, nullable=false)
     * @Assert\NotBlank()
     * @Gedmo\Versioned()
     */
    protected $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Tipo")
     * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $tipo;

    /**
     * @var string $codigo
     * @ORM\Column(name="codigo", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $codigo;

    /**
     * @var string $barcode
     * @ORM\Column(name="barcode", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $barcode;

    /**
     * @var string $nroSerie
     * @ORM\Column(name="nro_serie", type="string",unique=true, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nroSerie;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Estado")
     * @ORM\JoinColumn(name="estado_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $estado;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Marca",inversedBy="equipos")
     * @ORM\JoinColumn(name="marca_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $marca;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Modelo")
     * @ORM\JoinColumn(name="modelo_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $modelo;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Proveedor", inversedBy="equipos")
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $proveedor;

    /**
     * @var date $fechaCompra
     * @ORM\Column(name="fecha_compra", type="date", nullable=true)
     * @Gedmo\Versioned()
     */
    private $fechaCompra;

    /**
     * @var date $inicioVidaUtil
     * @ORM\Column(name="inicio_vida_util", type="date", nullable=true)
     * @Gedmo\Versioned()
     */
    private $inicioVidaUtil;

    /**
     * @var string $nroOrdenCompra
     * @ORM\Column(name="nro_orden_compra", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nroOrdenCompra;

    /**
     * @var string $nroFactura
     * @ORM\Column(name="nro_factura", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nroFactura;

    /**
     * @var string $nroRemito
     * @ORM\Column(name="nro_remito", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nroRemito;

    /**
     * @var integer $precio
     * @ORM\Column(name="precio", type="decimal", scale=2,nullable=true )
     */
    protected $precio;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Moneda")
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     */
    protected $moneda;

    /**
     * @var string $cotizacionDolar
     * @ORM\Column(name="cotizacion_dolar", type="decimal", scale=2, nullable=true)
     */
    protected $cotizacionDolar = 1;

    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;

    /**
     * @ORM\Column(name="verificado", type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $verificado = false;

    /**
     * @ORM\Column(name="eliminar", type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $eliminar = false;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RequerimientoDetalle", mappedBy="equipo",cascade={"remove"})
     */
    protected $requerimientos;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrdenTrabajoDetalle", mappedBy="equipo",cascade={"remove"})
     */
    protected $ordenesdetrabajo;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\EquipoUbicacion", mappedBy="equipo",cascade={"persist", "remove"})
     * @ORM\OrderBy({"created" = "DESC"})
     */
    protected $ubicaciones;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RecepcionCompraDetalle", mappedBy="equipo")
     */
    protected $detcompra;

    /**
     * @ORM\OneToOne(targetEntity="ConfigBundle\Entity\Importados")
     */
    private $importado;

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

    public function getAntiguedad() {
        //$adq = $this->getFechaAdquisicion() ? new \DateTime(UtilsController::toAnsiDate($this->getFechaAdquisicion(), '/')) : null;
        //$fecha = $this->getInicioVidaUtil() ? $this->getInicioVidaUtil() : $this->getFechaInstalacion() ? $this->getFechaInstalacion() : $adq;
        if ($this->getInicioVidaUtil()) {
            return UtilsController::calculaAntiguedad($this->getInicioVidaUtil());
        }
        return null;
    }

//    public function getVidaUtil() {
//        $adq = null;
//        if ($this->getFechaAdquisicion()) {
//            $adq = new \DateTime(UtilsController::toAnsiDate($this->getFechaAdquisicion(), '/'));
//            if (intval($adq->format('Y')) > 2019)
//                $adq = null;
//        }
////        $adq = $this->getFechaAdquisicion() ? new \DateTime(UtilsController::toAnsiDate($this->getFechaAdquisicion(), '/')) : null;
////        if (intval($adq->format('Y')) > 2019) {
////            $adq = null;
////        }
//        $fecha = $this->getInicioVidaUtil() ? $this->getInicioVidaUtil() : ($this->getFechaInstalacion() ? $this->getFechaInstalacion() : $adq);
//        return $fecha;
//    }

    public function getTexto() {
        return $this->codigo . ' - ' . $this->nombre . ' - ' . $this->nroSerie;
    }

    public function getTextoOT() {
        return $this->getTipo()->getNombre() . ' │ ' . $this->nombre . ' │ ' . $this->nroSerie . ' │ ' . $this->getMarca()->getNombre() . ' │ ' . $this->getModelo()->getNombre();
    }

    public function getTextoCompleto() {
        return $this->getTipo()->getNombre() . ' │ ' . $this->nombre . ' │ ' . $this->getMarca()->getNombre() . ' │ ' . $this->getModelo()->getNombre() . ' │ ' . $this->nroSerie;
    }

    public function getTipoMarcaModelo() {
        return $this->getTipo()->getNombre() . ' │ ' . $this->getMarca()->getNombre() . ' │ ' . $this->getModelo()->getNombre();
    }

    public function getCodigoItem() {
        return ( $this->barcode ) ? $this->barcode : $this->codigo;
    }

    public function getUbicacionActual() {
        foreach ($this->getUbicaciones() as $ubic) {
            if ($ubic->getActual()) {
                return $ubic;
            }
        }
        return $ubicActual;
    }

    /*     * *
     * DATOS PARA LISTADOS DE BIENES EN STOCK Y VALORIZADO
     */

    public function getOC() {
        return (count($this->getDetcompra()) > 0) ? $this->getDetcompra()[0]->getCompraDetalle() : null;
    }

    public function getOrdenCompra() {
        $oc = array('txt' => $this->getNroOrdenCompra(), 'id' => null);
        if ($this->getOC()) {
            $oc = array('txt' => $this->getOC()->getCompra()->getOrdenCompra(), 'id' => $this->getOC()->getCompra()->getId());
        }
        return $oc;
    }

    public function getCuenta() {
        return ($this->getOC()) ? $this->getOC()->getCompra()->getNroCuenta() : '';
    }

    public function getFactura() {
        return ($this->getOC()) ? $this->getOC()->getCompra()->getNroFactura() : $this->getNroFactura();
    }

    public function getRemito() {
        return ($this->getOC()) ? $this->getOC()->getNroRemitoEquipo() : $this->getNroRemito();
    }

    public function getRazonSocial() {
        return ($this->getOC()) ? $this->getOC()->getCompra()->getRazonSocial() : '';
    }

    public function getFechaAdquisicion() {
        $fecha = ($this->getFechaCompra()) ? $this->getFechaCompra()->format('d/m/Y') : '';
        return ($this->getOC()) ? $this->getOC()->getCompra()->getFechaCompra()->format('d/m/Y') : $fecha;
    }

    public function getPrecioEquipo() {
        $precio = $this->getPrecio();
        if ($this->getOC()) {
            $precio = ($this->getOC()->getPrecio() > 0) ? $this->getOC()->getPrecio() : $this->getPrecio();
        }
        return $precio;
    }

    public function getMonedaEquipo() {
        $moneda = $this->getMoneda() ? $this->getMoneda()->getAbreviatura() : null;
        if ($this->getOC()) {
            $moneda = $this->getOC()->getMoneda()->getAbreviatura();
        }
        return $moneda;
    }

    public function getCotizacionEquipo($cotiz = 1) {
        $cotizacion = ($this->getOC()) ? $this->getOC()->getCompra()->getCotizacionDolar() : $this->getCotizacionDolar();
        return ( $cotizacion == 1 || $cotizacion == 0 ) ? $cotiz : $cotizacion;
    }

    public function getPrecioDolares($cotiz = 1) {
        return ($this->getMonedaEquipo() == '$') ? $this->getPrecioEquipo() / $this->getCotizacionEquipo($cotiz) : $this->getPrecioEquipo();
    }

    public function getPrecioPesos($cotiz = 1) {
        return ($this->getMonedaEquipo() == 'U$S') ? $this->getPrecioEquipo() * $this->getCotizacionEquipo($cotiz) : $this->getPrecioEquipo();
    }

//    public function getFechaInstalacion() {
//        $fecha = '';
//        if ($this->getOrdenesdetrabajo()) {
//            foreach ($this->getOrdenesdetrabajo() as $ord) {
//                if ($fecha)
//                    break;
//                if ($ord->getTareas()) {
//                    foreach ($ord->getTareas() as $tar) {
//                        if (strpos($tar->getDescripcion(), '<strong>Operativo</strong>') !== false) {
//                            $fecha = $tar->getFecha();
//                        }
//                    }
//                }
//            }
//        }
//        return $fecha;
//    }

    /*     * 9j5nwg3 ************************** */

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
     * @return Equipo
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
     * Set codigo
     *
     * @param string $codigo
     * @return Equipo
     */
    public function setCodigo($codigo) {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo() {
        return $this->codigo;
    }

    /**
     * Set barcode
     *
     * @param string $barcode
     * @return Equipo
     */
    public function setBarcode($barcode) {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * Get barcode
     *
     * @return string
     */
    public function getBarcode() {
        return $this->barcode;
    }

    /**
     * Set nroSerie
     *
     * @param string $nroSerie
     * @return Equipo
     */
    public function setNroSerie($nroSerie) {
        $this->nroSerie = $nroSerie;

        return $this;
    }

    /**
     * Get nroSerie
     *
     * @return string
     */
    public function getNroSerie() {
        return $this->nroSerie;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Equipo
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
     * @return Equipo
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
     * @return Equipo
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
     * Set tipo
     *
     * @param \ConfigBundle\Entity\Tipo $tipo
     * @return Equipo
     */
    public function setTipo(\ConfigBundle\Entity\Tipo $tipo = null) {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return \ConfigBundle\Entity\Tipo
     */
    public function getTipo() {
        return $this->tipo;
    }

    /**
     * Set estado
     *
     * @param \ConfigBundle\Entity\Estado $estado
     * @return Equipo
     */
    public function setEstado(\ConfigBundle\Entity\Estado $estado = null) {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return \ConfigBundle\Entity\Estado
     */
    public function getEstado() {
        return $this->estado;
    }

    /**
     * Set marca
     *
     * @param \ConfigBundle\Entity\Marca $marca
     * @return Equipo
     */
    public function setMarca(\ConfigBundle\Entity\Marca $marca = null) {
        $this->marca = $marca;

        return $this;
    }

    /**
     * Get marca
     *
     * @return \ConfigBundle\Entity\Marca
     */
    public function getMarca() {
        return $this->marca;
    }

    /**
     * Set modelo
     *
     * @param \ConfigBundle\Entity\Modelo $modelo
     * @return Equipo
     */
    public function setModelo(\ConfigBundle\Entity\Modelo $modelo = null) {
        $this->modelo = $modelo;

        return $this;
    }

    /**
     * Get modelo
     *
     * @return \ConfigBundle\Entity\Modelo
     */
    public function getModelo() {
        return $this->modelo;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Equipo
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
     * @return Equipo
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
     * MANEJO DE FOTO
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
        return 'uploads/photos';
    }

    /**
     * @Assert\File(maxSize="3M", mimeTypes={"image/jpeg", "image/pjpeg", "image/png", "image/x-png"},
     *              mimeTypesMessage="El tipo de imagen no es válido. Debe ser .png o .jpg")
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
     * FIN MANEJO DE FOTO
     */

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Equipo
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
     * Set importado
     *
     * @param \ConfigBundle\Entity\Importados $importado
     * @return Equipo
     */
    public function setImportado(\ConfigBundle\Entity\Importados $importado = null) {
        $this->importado = $importado;

        return $this;
    }

    /**
     * Get importado
     *
     * @return \ConfigBundle\Entity\Importados
     */
    public function getImportado() {
        return $this->importado;
    }

    /**
     * Set fechaCompra
     *
     * @param \DateTime $fechaCompra
     * @return Equipo
     */
    public function setFechaCompra($fechaCompra) {
        $this->fechaCompra = $fechaCompra;

        return $this;
    }

    /**
     * Get fechaCompra
     *
     * @return \DateTime
     */
    public function getFechaCompra() {
        return $this->fechaCompra;
    }

    /**
     * Set nroFactura
     *
     * @param string $nroFactura
     * @return Equipo
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
     * @return Equipo
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
     * Set proveedor
     *
     * @param \AppBundle\Entity\Proveedor $proveedor
     * @return Equipo
     */
    public function setProveedor(\AppBundle\Entity\Proveedor $proveedor = null) {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \AppBundle\Entity\Proveedor
     */
    public function getProveedor() {
        return $this->proveedor;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->ubicaciones = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add ubicaciones
     *
     * @param \AppBundle\Entity\EquipoUbicacion $ubicaciones
     * @return Equipo
     */
    public function addUbicacion(\AppBundle\Entity\EquipoUbicacion $ubicaciones) {
        $ubicaciones->setEquipo($this);
        $this->ubicaciones[] = $ubicaciones;
        return $this;
    }

    /**
     * Remove ubicaciones
     *
     * @param \AppBundle\Entity\EquipoUbicacion $ubicaciones
     */
    public function removeUbicacion(\AppBundle\Entity\EquipoUbicacion $ubicaciones) {
        $this->ubicaciones->removeElement($ubicaciones);
    }

    /**
     * Get ubicaciones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUbicaciones() {
        return $this->ubicaciones;
    }

    /**
     * Set verificado
     *
     * @param boolean $verificado
     * @return Equipo
     */
    public function setVerificado($verificado) {
        $this->verificado = $verificado;

        return $this;
    }

    /**
     * Get verificado
     *
     * @return boolean
     */
    public function getVerificado() {
        return $this->verificado;
    }

    /**
     * Set eliminar
     *
     * @param boolean $eliminar
     * @return Equipo
     */
    public function setEliminar($eliminar) {
        $this->eliminar = $eliminar;

        return $this;
    }

    /**
     * Get eliminar
     *
     * @return boolean
     */
    public function getEliminar() {
        return $this->eliminar;
    }

    /**
     * Set nroOrdenCompra
     *
     * @param string $nroOrdenCompra
     * @return Equipo
     */
    public function setNroOrdenCompra($nroOrdenCompra) {
        $this->nroOrdenCompra = $nroOrdenCompra;

        return $this;
    }

    /**
     * Get nroOrdenCompra
     *
     * @return string
     */
    public function getNroOrdenCompra() {
        return $this->nroOrdenCompra;
    }

    /**
     * Add requerimientos
     *
     * @param \AppBundle\Entity\RequerimientoDetalle $requerimientos
     * @return Equipo
     */
    public function addRequerimiento(\AppBundle\Entity\RequerimientoDetalle $requerimientos) {
        $this->requerimientos[] = $requerimientos;

        return $this;
    }

    /**
     * Remove requerimientos
     *
     * @param \AppBundle\Entity\RequerimientoDetalle $requerimientos
     */
    public function removeRequerimiento(\AppBundle\Entity\RequerimientoDetalle $requerimientos) {
        $this->requerimientos->removeElement($requerimientos);
    }

    /**
     * Get requerimientos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRequerimientos() {
        return $this->requerimientos;
    }

    /**
     * Add ordenesdetrabajo
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $ordenesdetrabajo
     * @return Equipo
     */
    public function addOrdenesdetrabajo(\AppBundle\Entity\OrdenTrabajoDetalle $ordenesdetrabajo) {
        $this->ordenesdetrabajo[] = $ordenesdetrabajo;

        return $this;
    }

    /**
     * Remove ordenesdetrabajo
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $ordenesdetrabajo
     */
    public function removeOrdenesdetrabajo(\AppBundle\Entity\OrdenTrabajoDetalle $ordenesdetrabajo) {
        $this->ordenesdetrabajo->removeElement($ordenesdetrabajo);
    }

    /**
     * Get ordenesdetrabajo
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrdenesdetrabajo() {
        return $this->ordenesdetrabajo;
    }

    public function getEnOrdenAbierta($id = null) {
        foreach ($this->getOrdenesdetrabajo() as $otDet) {
            if ($otDet->getOrdenTrabajo()->getEstado() == 'ABIERTO' && $otDet->getEntregado() == 0) {
                if ($otDet->getOrdenTrabajo()->getId() == $id) {
                    continue;
                }
                return true;
            }
        }
        return false;
    }

    public function getEnRequerimientoAbierto($id = null) {
        foreach ($this->getRequerimientos() as $req) {
            if (in_array($req->getRequerimiento()->getEstado(), array('SIN ASIGNAR', 'ASIGNADO'))) {
                if ($req->getRequerimiento()->getId() == $id) {
                    continue;
                }
                if ($req->getOrdenTrabajoDetalle()) {
                    if ($req->getOrdenTrabajoDetalle()->getEntregado()) {
                        continue;
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get detcompra
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetcompra() {
        return $this->detcompra;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return Equipo
     */
    public function setPrecio($precio) {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return string
     */
    public function getPrecio() {
        return $this->precio;
    }

    /**
     * Set cotizacionDolar
     *
     * @param string $cotizacionDolar
     * @return Equipo
     */
    public function setCotizacionDolar($cotizacionDolar) {
        $this->cotizacionDolar = $cotizacionDolar;

        return $this;
    }

    /**
     * Get cotizacionDolar
     *
     * @return string
     */
    public function getCotizacionDolar() {
        return $this->cotizacionDolar;
    }

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return Equipo
     */
    public function setMoneda(\ConfigBundle\Entity\Moneda $moneda = null) {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return \ConfigBundle\Entity\Moneda
     */
    public function getMoneda() {
        return $this->moneda;
    }

    /**
     * Set inicioVidaUtil
     *
     * @param \DateTime $inicioVidaUtil
     * @return Equipo
     */
    public function setInicioVidaUtil($inicioVidaUtil) {
        $this->inicioVidaUtil = $inicioVidaUtil;

        return $this;
    }

    /**
     * Get inicioVidaUtil
     *
     * @return \DateTime
     */
    public function getInicioVidaUtil() {
        return $this->inicioVidaUtil;
    }

    /**
     * Add ubicaciones
     *
     * @param \AppBundle\Entity\EquipoUbicacion $ubicaciones
     * @return Equipo
     */
    public function addUbicacione(\AppBundle\Entity\EquipoUbicacion $ubicaciones) {
        $this->ubicaciones[] = $ubicaciones;

        return $this;
    }

    /**
     * Remove ubicaciones
     *
     * @param \AppBundle\Entity\EquipoUbicacion $ubicaciones
     */
    public function removeUbicacione(\AppBundle\Entity\EquipoUbicacion $ubicaciones) {
        $this->ubicaciones->removeElement($ubicaciones);
    }

    /**
     * Add detcompra
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $detcompra
     * @return Equipo
     */
    public function addDetcompra(\AppBundle\Entity\RecepcionCompraDetalle $detcompra) {
        $this->detcompra[] = $detcompra;

        return $this;
    }

    /**
     * Remove detcompra
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $detcompra
     */
    public function removeDetcompra(\AppBundle\Entity\RecepcionCompraDetalle $detcompra) {
        $this->detcompra->removeElement($detcompra);
    }

}