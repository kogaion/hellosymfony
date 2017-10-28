<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 9/15/2017
 * Time: 12:58 PM
 */

namespace CourseraBundle\Presentation\Controller;


use Goutte\Client;
use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

use CourseraBundle\Domain\Model\Week;
use CourseraBundle\Domain\Model\Material;

class LoginController extends Controller
{
    /**
     * @return Response
     */
    public function loginAction()
    {
        try {

            $weeks = $this->getWeeks();
            $lectures = $this->getLectures($weeks);

            $links = [];
            foreach ($lectures as $l) {
                $link = (str_replace(
                    ['{{lecture-id}}', '{{lecture-slug}}'],
                    [$l->getId(), $l->getSlug()],
                    $this->getParameter('app.course_page_lecture')
                ));
                $links[] = '<a target="_blank" href="' . $link . '">' . substr($link, strrpos($link, '/') + 1) . '</a>';
            }

            return $this->getResponse('<html><body>' . join('<br />', $links) . "</body></html>");
        } catch (Exception $e) {
            return $this->getResponse($e->getMessage(), 404);
        }
    }

    /**
     * @param Material $lecture
     * @return mixed|null
     */
    protected function getSlides($lecture)
    {
        $link = str_replace(
            ['{{lecture-id}}', '{{lecture-slug}}'],
            [$lecture->getId(), $lecture->getSlug()],
            $this->getParameter('app.course_page_lecture')
        );

        $cache = $this->getCache();
        $cacheKey = md5(__METHOD__ . '|' . $link . '| v1');

        $slides = $cache->get($cacheKey);
        if (empty($slides)) {
            $slides = $this->extractSlides($link);
            $cache->set($cacheKey, $slides);
        }

        return $slides;
    }

    /**
     * @return Week[]
     */
    protected function getWeeks()
    {
        $cache = $this->getCache();
//        $cache->clear();

        $cacheKey = md5(__METHOD__ . '| v2');
        $courseMaterials = $cache->get($cacheKey);

        if (empty($courseMaterials)) {
            $courseMaterials = $this->extractMaterials();
            $cache->set($cacheKey, $courseMaterials, 720000);
        }

        $weeks = [];
        foreach ($courseMaterials as $material) {
            $weeks[] = Week::build($material);
        }

        return $weeks;
    }

    /**
     * @return array
     * @internal param Client $client
     */
    protected function extractMaterials()
    {
        $client = $this->initClient();

        $gastronomyUrl = $this->getParameter('app.coursera_page_gastronomy');
        $crawler = $client->request('GET', $gastronomyUrl);
        if (!preg_match('/Week 1/i', $client->getResponse()->getContent())) {
            $this->throwException('Could not go to week 1');
        }

        $json = $crawler->filterXPath('//script')->each(function (Crawler $node) {
            $json = null;
            $text = $node->getNode(0)->textContent;
            if (preg_match('/window\.App\=/i', $text)) {
                $text = str_replace(['window.App='], [''], $text);
                $text = substr($text, 0, strpos($text, 'window.appName=') - 1);
                $text = trim($text, "; \n");
                $json = json_decode($text, true, 1024);
                return $json;
            }
            return $json;
        });
        if (empty($json)) {
            $this->throwException('Could not parse json response');
        }

        $materials = $json[7]['context']['dispatcher']['stores']['CourseStore']['rawCourseMaterials']['courseMaterialsData']['elements'];
        if (empty($materials)) {
            $this->throwException('Could not extract materials');
        }

        return $materials;
    }

    /**
     * @param Week[] $weeks
     * @return Material[]
     */
    protected function getLectures($weeks)
    {
        $lectures = [];

        for ($i = 0, $cntW = count($weeks); $i < $cntW; $i++) {

            $w = $weeks[$i];
            $modules = $w->getModules();
            if (empty($modules)) {
                continue;
            }

            for ($j = 0, $cntM = count($modules); $j < $cntM; $j++) {

                $m = $modules[$j];
                $materials = $m->getMaterials();
                if (empty($materials)) {
                    continue;
                }

                foreach ($materials as $mat) {
                    if (!$mat->isLecture()) {
                        continue;
                    }
                    $lectures[] = $mat;
                }
            }
        };
        return $lectures;
    }

    /**
     * @param Client $client
     * @return Client
     * @throws Exception
     */
    protected function login($client)
    {
        $userName = $this->getParameter('app.coursera_username');
        $passWord = $this->getParameter('app.coursera_password');
        $csrfCookieName = $this->getParameter('app.coursera_csrf_cookie_name');
        $loginUrl = $this->getParameter('app.coursera_page_login');
        $homeUrl = $this->getParameter('app.coursera_page_home');

        $client->request('GET', $homeUrl);
        $cookieJar = $client->getCookieJar();
        $csrfCookie = $cookieJar->get($csrfCookieName)->getValue();
        if (empty($csrfCookie)) {
            $this->throwException('Could not get the CSRF cookie');
        }

        $loginUrl = str_replace(['{{csrf-token}}'], [$csrfCookie], $loginUrl);
        $client->request('POST', $loginUrl, ['email' => $userName, 'password' => $passWord]);
        if (!preg_match('/My Coursera/i', $client->getResponse()->getContent())) {
            $this->throwException('Could not login into Coursera');
        }

        return $client;
    }

    /**
     * @return Client
     * @throws Exception
     */
    protected function initClient()
    {
        static $client;

        if (empty($client)) {
            $client = $this->getClient();

            $i = 0;
            $loggedIn = false;
            $exception = null;
            while (!$loggedIn && $i++ < 3) {
                try {
                    $client = $this->login($client);
                    $loggedIn = true;
                } catch (Exception $e) {
                    $exception = $e;
                    dump('Login failed. trying to relogin ... ' . $e->getMessage());
                }
            }

            if (!$loggedIn && null !== $exception) {
                throw $exception;
            }
        }

        return $client;
    }

    /**
     * @param string $msg
     * @param int $httpCode
     * @return Response
     */
    protected function getResponse($msg, $httpCode = 200)
    {
        return new Response($msg, $httpCode);
    }

    /**
     * @return FilesystemCache
     */
    protected function getCache()
    {
        return new FilesystemCache();
    }

    /**
     * @param string $msg
     * @throws Exception
     */
    protected function throwException($msg)
    {
        throw new Exception($msg);
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        $client = new Client();
        $config = $client->getClient()->getConfig();
        $client->setClient(new \GuzzleHttp\Client(
            $config + [
                'timeout' => 15
            ]
        ));
        return $client;
    }

}