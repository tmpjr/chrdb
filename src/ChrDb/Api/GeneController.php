<?php 

namespace ChrDb\Api;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use InvalidArgumentException;

class GeneController
{
	public function searchAction(Request $request, Application $app)
	{
		$term = $request->get('term');
		$token = "%" . $term . "%";
		$results = $app['db']->fetchAll("SELECT * FROM gene WHERE symbol LIKE :term OR synonyms LIKE :term OR full_name LIKE :term", array(':term' => $token));
		return new JsonResponse($results);
	}

	public function fetchAction(Request $request, Application $app)
	{
		$id = intval($request->get('id'));
		$stmt = $app['db']->executeQuery('SELECT * FROM gene WHERE gene_id = ?', array($id));
		$result = $stmt->fetch();
		return new JsonResponse($result);
	}
}