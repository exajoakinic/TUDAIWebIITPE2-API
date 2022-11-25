<?php

class ApiView {

  public function response($data, $status = 200) {
    header("Content-Type: application/json");
    header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));

      // convierte los datos a un formato json
      // if(isset($data)){
      //     $json= array(
      //       "total productos"=>count($data),
      //       "status"=>$status
      //    );
      //     echo json_encode($json);
      //     }

      echo json_encode($data);
  }

  private function _requestStatus($code){
    $status = array(
      200 => "OK",
      201 => "Created",
      400 => "Bad request",
      401 => "Unauthorized",
      403 => "Forbidden",
      404 => "Not found",
      500 => "Internal Server Error"
    );
    return $status[$code] ?? $status[500];
  }
}