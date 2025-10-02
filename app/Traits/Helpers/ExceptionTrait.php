<?php

namespace App\Traits\Helpers;

use ErrorException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait ExceptionTrait
{
    use ResponseJsonTrait;

    public function apiExceptions($request,$e)
    {

        if($this->isQuery($e)){
            return $this->QueryResponse($e);
        }

        if($this->isModel($e)){
            return $this->ModelResponse($e);
        }

        if ($this->notAllowed($e)) {
            return $this->NotAllowedResponse($e);
        }

        if($this->isHttp($e)){
            return $this->HttpResponse($e);
        }

        if($this->isAuthentication($e)){
            return $this->AuthenticationResponse($e);
        }

        if($this->isUnauthorized($e)){
            return $this->UnauthorizedResponse($e);
        }

        if($this->isValidation($e,$request)){
            return $this->ValidationResponse($e,$request);
        }

        if($this->isTokenMismatch($e)){
            return $this->TokenMismatchResponse($e);
        }

        if($this->isError($e)){
            return $this->ErrorsResponse($e);
        }
        else{
            return $this->ErrorsResponse($e);
        }
    }

    protected function isError($e){
        return $e instanceof ErrorException;
    }

    protected function isModel($e){
        return $e instanceof ModelNotFoundException;
    }

    protected function isQuery($e){
        return $e instanceof QueryException;
    }

    protected function isHttp($e){
        return $e instanceof NotFoundHttpException;
    }

    protected function isAuthentication($e){
        return $e instanceof AuthenticationException;
    }

    protected function isUnauthorized($e){
        return $e instanceof UnauthorizedHttpException;
    }

    protected function isValidation($e){
        return $e instanceof ValidationException;
    }

    protected function isTokenMismatch($e){
        return $e instanceof TokenMismatchException;
    }

    protected function notAllowed($e)
    {
        return $e instanceof MethodNotAllowedHttpException;
    }

    protected function ModelResponse($e){
        return $this->errorResponse('Aucun résultat trouvé dans les enrégistrement d\'' . strtolower(str_replace('App\\Models\\','',$e->getModel())), $e, Response::HTTP_NOT_FOUND);
    }

    protected function QueryResponse($e){
        return $this->errorResponse($e->getMessage(), [], Response::HTTP_NOT_FOUND);
    }

    protected function NotAllowedResponse($e)
    {
        return $this->errorResponse($e->getMessage(), [], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    protected function ErrorsResponse($e)
    {
        return $this->errorResponse($e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function HttpResponse($e)
    {
        return $this->errorResponse("Route inconnue. Veuillez revérifier la route de votre requête", [], Response::HTTP_NOT_FOUND);
    }

    protected function AuthenticationResponse($e)
    {
        return $this->errorResponse("Connexion expiré. Veuillez vous connectez", [], Response::HTTP_UNAUTHORIZED);
        return $this->errorResponse($e->getMessages() == "Unauthenticated" ? "Connexion expiré. Veuillez vous connectez" : $e->getMessages(), [], Response::HTTP_UNAUTHORIZED);
    }

    protected function UnauthorizedResponse($e)
    {
        return $this->errorResponse($e->getMessages() + "Vous n'avez pas le droit d'éffectuer cette année", [], Response::HTTP_FORBIDDEN);
    }

    protected function ValidationResponse($e,$request)
    {
        return $this->errorResponse("Erreur de validation du formulaire", $e->validator->errors()->getMessages(), Response::HTTP_UNPROCESSABLE_ENTITY);

        $errors = $e->validator->errors()->getMessages();
        return response()->json([
            "errors" =>[ "message" => [$errors]]
        ],Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function TokenMismatchResponse($e){
        return $this->errorResponse("Votre session a expiré, veuillez vous reconnecté", [], 419);
    }
}
