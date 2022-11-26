<?php

namespace ConfigBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\JsonResponse;

class UtilsController extends Controller {

    // controla permiso para acceso a ruta
    public static function haveAccess($user, $route) {
        if ($user) {
            if ($user->getAccess($route) or $user->getRol()->getAdmin()) {
                return TRUE;
            }
            throw new AccessDeniedException('No posee permiso para acceder a esta página!');
        }
        else {
            throw new AccountExpiredException('Su sesión de usuario ha expirado! Debe volver a iniciar sesión.');
        }
    }

    public static function ultimoMesParaFiltro($desde, $hasta, $todo = true) {
        $hoy = new \DateTime();
        if ($todo) {
            $inicio = date("d-m-Y", strtotime('01-11-2021'));
        }
        else {
            $inicio = date("d-m-Y", strtotime($hoy->format('d-m-Y') . "- 30 days"));
        }
        $ini = ($desde) ? $desde : $inicio;
        $fin = ($hasta) ? $hasta : $hoy->format('d-m-Y');
        return array('desde' => $ini, 'hasta' => $fin);
    }

    /// PARA FECHAS
    public static function toAnsiDate($value, $sep = '-') {
        if (is_array($value)) {
            $value = isset($value['text']) ? $value['text'] : null;
        }
        if (strpos($value, $sep) === false) {
            return $value;
        }
        $date = UtilsController::toArray($value, $sep);

        $ansi = $date['Y'] . '-' . $date['m'] . '-' . $date['d'];
        //$ansi .= isset($date['H']) ? ' '.$date['H'].':'.$date['i'].':'.$date['s'] : '';
        return $ansi;
    }

    public static function longDateSpanish($fecha, $dayname = FALSE) {
        $date = strtotime($fecha->format('Y-m-d'));
        $dia = date("l", $date);

        if ($dia == "Monday")
            $dia = "Lunes";
        if ($dia == "Tuesday")
            $dia = "Martes";
        if ($dia == "Wednesday")
            $dia = "Miércoles";
        if ($dia == "Thursday")
            $dia = "Jueves";
        if ($dia == "Friday")
            $dia = "Viernes";
        if ($dia == "Saturday")
            $dia = "Sabado";
        if ($dia == "Sunday")
            $dia = "Domingo";

        $dia2 = date("d", $date);

        $mes = date("F", $date);

        if ($mes == "January")
            $mes = "Enero";
        if ($mes == "February")
            $mes = "Febrero";
        if ($mes == "March")
            $mes = "Marzo";
        if ($mes == "April")
            $mes = "Abril";
        if ($mes == "May")
            $mes = "Mayo";
        if ($mes == "June")
            $mes = "Junio";
        if ($mes == "July")
            $mes = "Julio";
        if ($mes == "August")
            $mes = "Agosto";
        if ($mes == "September")
            $mes = "Setiembre";
        if ($mes == "October")
            $mes = "Octubre";
        if ($mes == "November")
            $mes = "Noviembre";
        if ($mes == "December")
            $mes = "Diciembre";

        $ano = date("Y", $date);
        if ($dayname)
            $fecha = "$dia, $dia2 de $mes de $ano";
        else
            $fecha = "$dia2 de $mes de $ano";

        return $fecha;
    }

    public static function toArray($value, $sep = '-') {
        if (strpos($value, $sep) === false) {
            return array('Y' => '1969', 'm' => '01', 'd' => '01');
        }
        $parts = explode($sep, $value);
        $years = explode(' ', $parts[2]);
        //    $hours = isset($years[1]) ? explode(':',$years[1]) : null;

        $date = array('d' => $parts[0], 'm' => $parts[1], 'Y' => $years[0]);
        //       if($hours)
        //       $date = array_merge($date,array('H'=>$hours[0],'i'=>$hours[1],'s'=>$hours[2]));

        return $date;
    }

    //// TRUNCADO DE CADENAS
    public static function myTruncate($string, $limit, $break = " ", $pad = "…") {
        // return with no change if string is shorter than $limit
        if (strlen($string) <= $limit) {
            return $string;
        }
        // is $break present between $limit and the end of the string?
        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        } return $string;
    }

    public static function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();

        if ($form->count() > 0) {
            foreach ($form->all() as $child) {
                /**
                 * @var \Symfony\Component\Form\Form $child
                 */
                if (!$child->isValid()) {
                    $errors[$child->getName()] = UtilsController::getErrorMessages($child);
                }
            }
        }
        else {
            /**
             * @var \Symfony\Component\Form\FormError $error
             */
            foreach ($form->getErrors() as $key => $error) {
                $errors[] = $error->getMessage();
            }
        }

        return $errors;
    }

    public static function errorMessage($codError) {
        switch ($codError) {
            case 1062:
                $msg = 'El dato que intenta ingresar está duplicado.';
                break;
            case 1451:
                $msg = 'Este dato no puede ser eliminado porque está siendo utilizado en el sistema.';
                break;
            default:
                $msg = 'No se puede realizar esta acción. Código de Error:' . $codError;
                break;
        }
        return $msg;
    }

    public static function arrayMoveElements($array, $from, $to) {
        if ($from == $to) {
            return $array;
        }
        $c = count($array);
        if (($c > $from) and ( $c > $to)) {
            if ($from < $to) {
                $f = $array[$from];
                for ($i = $from; $i < $to; $i++) {
                    $array[$i] = $array[$i + 1];
                }
                $array[$to] = $f;
            }
            else {
                $f = $array[$from];
                for ($i = $from; $i > $to; $i--) {
                    $array[$i] = $array[$i - 1];
                }
                $array[$to] = $f;
            }
        }
        return $array;
    }

    /**
     * FUNCION PARA CHECKEAR IP MEDIANTE PING
     */
    public static function checkIP($ip, $n) {
        $err1 = 'Host de destino inaccesible';
        $err2 = 'Destination Host Unreachable';
        $err3 = 'Tiempo de espera agotado para esta solicitud';
        $err4 = 'Request timed out';
        /*
          $processText = addslashes('psexec \\').'192.168.0.112'.' -u '.$user.' -p '.$pass.' -h -s ping -n '.$n.' '.$ip ;
          var_dump($processText);
          $process = new Process($processText);
          $process->run();
          var_dump($process->getOutput());
          die;

          $process = new Process('powershell Invoke-Command -ComputerName localhost -ScriptBlock {ping -n 1 192.168.0.25}');
          $process->run();
          var_dump($process->getOutput());
          die;

          if( $clientIp){
          $processText = addslashes('psexec \\').'192.168.0.25'.' -h -s ping -n '.$n.' '.$ip ;
          $process = new Process($processText);
          //$process = new Process('psexec \\\192.168.0.25 ping -n 1 192.168.0.25');
          $process->run();
          var_dump($process->getOutput());
          die;
          } */
        $process = new Process('ping -n ' . $n . ' ' . $ip);
        $process->run();
        $resultado = $salidaPing = $process->getOutput();
        $estado = 'danger';
        $bg = 'bg-red';
        $recibidos = 0;
        $ms = '';
        if ($resultado == '') {
            $resultado = 'Error al procesar la petición';
        }
        elseif (strpos($resultado, $err1) || strpos($resultado, $err2)) {
            $resultado = ' ' . $err1;
        }
        elseif (strpos($resultado, $err3) || strpos($resultado, $err4)) {
            $resultado = ' ' . $err3;
        }
        else {
            // porción paquetes
            if (strpos($resultado, 'Paquetes:')) {
                $paquetes_inicio = strpos($resultado, 'Paquetes:');
                $paquetes_fin = strpos($resultado, ')') + 1;
                $paquetes_long = $paquetes_fin - $paquetes_inicio;
                $paquetes = substr($resultado, $paquetes_inicio, $paquetes_long);
                // paquetes recibidos
                $recibidos = substr($paquetes, strpos($paquetes, 'recibidos = ') + 12, 1);
            }
            else {
                $paquetes_inicio = strpos($resultado, 'Packets:');
                $paquetes_fin = strpos($resultado, ')') + 1;
                $paquetes_long = $paquetes_fin - $paquetes_inicio;
                $paquetes = substr($resultado, $paquetes_inicio, $paquetes_long);
                // paquetes recibidos
                $recibidos = substr($paquetes, strpos($paquetes, 'Received = ') + 11, 1);
            }
            if ($recibidos < $n) {
                $estado = 'warning';
                $bg = 'bg-yellow2';
            }
            else {
                $estado = 'success';
                $bg = 'bg-green';
            }
            // porción tiempos
            if (strpos($resultado, 'Tiempos')) {
                $tiempo_inicio = strpos($resultado, 'Tiempos');
                $tiempo = ( substr($resultado, $tiempo_inicio) );
                // tiempo
                $ms = substr($tiempo, strpos($tiempo, 'Media = ') + 8);
            }
            else {
                $tiempo_inicio = strpos($resultado, 'Approximate');
                $tiempo = ( substr($resultado, $tiempo_inicio) );
                // tiempo
                $ms = substr($tiempo, strpos($tiempo, 'Average = ') + 10);
            }

            $resultado = utf8_decode(' Recibidos=' . $recibidos . '/' . $n . chr(13) . ' Tiempo=' . $ms);
        }

        $salida = array('text' => utf8_decode('Test IP ' . $ip . chr(13) . ' ' . $resultado), 'estado' => $estado, 'bg' => $bg,
            'paquetesRecibidos' => $recibidos, 'tiempo' => $ms, 'resultado' => $resultado, 'salidaPing' => $salidaPing);
        return $salida;
    }

    public static function calculaAntiguedad($fecha) {
        list($Y, $m, $d) = explode("-", $fecha->format("Y-m-d"));
        $edad = date("md") < $m . $d ? date("Y") - $Y - 1 : date("Y") - $Y;
        $txt = $edad . ($edad === 1 ? ' año' : ' años');
        return $txt;
    }

}