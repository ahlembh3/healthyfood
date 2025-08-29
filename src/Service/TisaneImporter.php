<?php
namespace App\Service;

use App\Entity\Tisane;
use App\Repository\TisaneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TisaneImporter
{
public function __construct(
private HttpClientInterface $http,
private EntityManagerInterface $em,
private TisaneRepository $tisanes
) {}

/**
* Importe des tisanes depuis la catégorie Wikipedia "Herbal tea".
* @param int $limit nombre d’articles max à importer
*/
public function importFromWikipedia(int $limit = 30): int
{
// 1) Récupérer les pages de la catégorie
$cm = $this->http->request('GET', 'https://en.wikipedia.org/w/api.php', [
'headers' => ['User-Agent' => 'HealthyFood/1.0 (contact@example.com)'],
'query' => [
'action'   => 'query',
'format'   => 'json',
'list'     => 'categorymembers',
'cmtitle'  => 'Category:Herbal tea',
'cmtype'   => 'page',
'cmlimit'  => min($limit, 50),
],
])->toArray(false);

$members = $cm['query']['categorymembers'] ?? [];
if (!$members) return 0;

// 2) Charger les détails (extrait + miniature) par lots
$pageIds = array_map(fn($m) => $m['pageid'], $members);
$chunks  = array_chunk($pageIds, 20);
$created = 0;

foreach ($chunks as $chunk) {
$pages = $this->http->request('GET', 'https://en.wikipedia.org/w/api.php', [
'headers' => ['User-Agent' => 'HealthyFood/1.0 (contact@example.com)'],
'query' => [
'action'       => 'query',
'format'       => 'json',
'prop'         => 'extracts|pageimages',
'exintro'      => 1,
'explaintext'  => 1,
'pithumbsize'  => 800,
'pageids'      => implode('|', $chunk),
],
])->toArray(false);

$pagesData = $pages['query']['pages'] ?? [];
foreach ($pagesData as $p) {
$title   = $p['title'] ?? null;
if (!$title) continue;

// éviter les doublons (par nom)
if ($this->tisanes->findOneBy(['nom' => $title])) continue;

$thumb   = $p['thumbnail']['source'] ?? null;
$extract = $p['extract'] ?? '';

$tisane = (new Tisane())
->setNom($title)
// On met un mode de préparation par défaut + l’extrait pour donner du contexte
->setModePreparation("Infuser 5–10 min dans l’eau chaude. Source: Wikipedia.\n\n".$extract);

if ($thumb) {
$tisane->setImage($thumb);
}

$this->em->persist($tisane);
$created++;
}
}

$this->em->flush();
return $created;
}
}
