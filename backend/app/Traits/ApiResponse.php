<?php

namespace App\Traits;

use CodeIgniter\HTTP\Response;

/**
 * Trait para respuestas API estándar del proyecto.
 *
 * Proporciona métodos para retornar respuestas consistentes en formato:
 * {
 *   "status": "success",
 *   "message": "...",
 *   "data": ...
 * }
 */
trait ApiResponse
{
    /**
     * Respuesta exitosa.
     *
     * @param mixed $data Datos a retornar
     * @param string $message Mensaje de éxito
     * @param int $statusCode Código HTTP
     * @return mixed
     */
    protected function respondSuccess(mixed $data = null, string $message = 'Operation successful', int $statusCode = Response::HTTP_OK)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->response->setJSON($response)->setStatusCode($statusCode);
    }

    /**
     * Respuesta con lista de recursos.
     *
     * @param mixed $data Datos o colección
     * @param string $message Mensaje
     * @param int $statusCode Código HTTP
     * @return mixed
     */
    protected function respondWithCollection(mixed $data, string $message = 'Data retrieved successfully', int $statusCode = Response::HTTP_OK)
    {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ])->setStatusCode($statusCode);
    }

    /**
     * Respuesta de error.
     *
     * @param string $message Mensaje de error
     * @param int $statusCode Código HTTP
     * @param mixed $errors Errores adicionales
     * @return mixed
     */
    protected function respondError(string $message = 'An error occurred', int $statusCode = Response::HTTP_BAD_REQUEST, mixed $errors = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->response->setJSON($response)->setStatusCode($statusCode);
    }

    /**
     * Respuesta de validación fallida.
     *
     * @param array $errors Errores de validación
     * @param string $message Mensaje
     * @return mixed
     */
    protected function respondValidationError(array $errors, string $message = 'Validation failed')
    {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ])->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Respuesta de recurso no encontrado.
     *
     * @param string $message Mensaje
     * @return mixed
     */
    protected function respondNotFound(string $message = 'Resource not found')
    {
        return $this->respondError($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Respuesta de no autorizado.
     *
     * @param string $message Mensaje
     * @return mixed
     */
    protected function respondUnauthorized(string $message = 'Unauthorized')
    {
        return $this->respondError($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Respuesta de prohibido.
     *
     * @param string $message Mensaje
     * @return mixed
     */
    protected function respondForbidden(string $message = 'Forbidden')
    {
        return $this->respondError($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Respuesta de creado exitosamente.
     *
     * @param mixed $data Datos del recurso creado
     * @param string $message Mensaje
     * @return mixed
     */
    protected function respondCreated(mixed $data = null, string $message = 'Resource created successfully')
    {
        return $this->respondSuccess($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Respuesta de actualizado exitosamente.
     *
     * @param mixed $data Datos del recurso actualizado
     * @param string $message Mensaje
     * @return mixed
     */
    protected function respondUpdated(mixed $data = null, string $message = 'Resource updated successfully')
    {
        return $this->respondSuccess($data, $message, Response::HTTP_OK);
    }

    /**
     * Respuesta de eliminado exitosamente.
     *
     * @param string $message Mensaje
     * @return mixed
     */
    protected function respondDeleted(string $message = 'Resource deleted successfully')
    {
        return $this->respondSuccess(null, $message, Response::HTTP_NO_CONTENT);
    }
}