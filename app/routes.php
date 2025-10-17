<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('API Poulailler');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });



    $app->get('/GetAllpoule', function (Request $request, Response $response) {

    $db = $this->get(PDO::class);
    $sth = $db->prepare("SELECT * FROM `poule`");
    $sth->execute();
    $data = $sth->fetchAll(PDO::FETCH_ASSOC);
    $payload = json_encode($data);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});




    $app->get('/GetAllpouleById/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $db = $this->get(PDO::class);

        $sth = $db->prepare("SELECT * FROM `poule` WHERE idPOULE = :idpoule");
        $sth->bindParam(':idpoule', $id, PDO::PARAM_INT);
        $sth->execute();

        $data = $sth->fetch(PDO::FETCH_ASSOC);
        $payload = json_encode($data);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->get('/GetPouleByRfid/{rfid}', function (Request $request, Response $response, array $args) {
        $rfid = $args['rfid'];
        $db = $this->get(PDO::class);

        $sth = $db->prepare("SELECT * FROM `poule` WHERE rfid = :rfid");
        $sth->bindParam(':rfid', $rfid, PDO::PARAM_STR);
        $sth->execute();

        $data = $sth->fetch(PDO::FETCH_ASSOC);

        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });






    $app->post('/AddPoule', function (Request $request, Response $response, array $args) {
        $input = $request->getParsedBody();
        $nom = $input['nom'];
        $date_d_enregistrement = $input['date_d_enregistrement'];
        $race = $input['race'];

        $db = $this->get(PDO::class);
        $sth = $db->prepare("INSERT INTO `poule` (`nom`, `date_d_enregistrement`, `race`) VALUES (:nom, :date_d_enregistrement, :race)");

        $sth->bindParam(':nom', $nom);
        $sth->bindParam(':date_d_enregistrement', $date_d_enregistrement);
        $sth->bindParam(':race', $race);

        $sth->execute();

        $insertedID = $db->lastInsertId();
        $payload = json_encode([
            'message' => 'Class ajouté avec succès',
            'idPOULE' => $insertedID
        ]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });





    $app->delete('/deletePoule/{id}', function (Request $request, Response $response, array $args) {
        $id = $args['id'];

        $db = $this->get(PDO::class);

        // Vérifie si la poule existe
        $check = $db->prepare("SELECT * FROM `poule` WHERE idPoule = :idpoule");
        $check->bindParam(':idpoule', $id, PDO::PARAM_INT);
        $check->execute();
        $poule = $check->fetch(PDO::FETCH_ASSOC);

        if (!$poule) {
            $payload = json_encode(['error' => 'Poule non trouvée']);
            $response->getBody()->write($payload);
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Suppression
        $sth = $db->prepare("DELETE FROM `poule` WHERE idPoule = :idpoule");
        $sth->bindParam(':idpoule', $id, PDO::PARAM_INT);
        $sth->execute();

        $payload = json_encode(['message' => 'Poule supprimée avec succès']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });

};
