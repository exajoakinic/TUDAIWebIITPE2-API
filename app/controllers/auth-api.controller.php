<?php
require_once './app/models/user.model.php';
require_once './app/views/api.view.php';
require_once './app/helpers/auth-api.helper.php';

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}


class AuthApiController
{
    private const key = '`UNH.T}hi;p@*KASDyz*zlC_B%#"S(cmZ))=aIk*j1rC9Jd=65mdf{wYH]f<Vng';
    private const expirationTime = 3600;

    private $userModel;
    private $view;
    private $authHelper;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->view = new ApiView();
        $this->authHelper = new AuthApiHelper();
    }

    public function getToken($params = null)
    {
        // Obtener "Basic base64(user:pass)
        $basic = $this->authHelper->getAuthHeader();

        if (empty($basic)) {
            $this->view->response('No autorizado', 401);
            return;
        }
        $basic = explode(" ", $basic); // ["Basic" "base64(user:pass)"]
        if ($basic[0] != "Basic") {
            $this->view->response('La autenticación debe ser Basic', 401);
            return;
        }

        //validar usuario:contraseña
        $userpass = base64_decode($basic[1]); // user:pass
        $userpass = explode(":", $userpass);

        $name = $userpass[0];
        $pass = $userpass[1];
        $user = $this->userModel->getBy("user", $name);
        if ($user && password_verify($pass, $user->password)) {
            //  crear un token
            $header = array(
                'alg' => 'HS256',
                'typ' => 'JWT'
            );
            $payload = array(
                'id' => $user->id,
                'name' => $user->user,
                'exp' => time() + self::expirationTime
            );
            $header = base64url_encode(json_encode($header));
            $payload = base64url_encode(json_encode($payload));
            $signature = hash_hmac('SHA256', "$header.$payload", self::key, true);
            $signature = base64url_encode($signature);
            $token = "$header.$payload.$signature";
            $this->view->response($token);
        } else {
            $this->view->response('No autorizado', 401);
        }
    }
}