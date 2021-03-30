<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function getData(){
        $date = Carbon::now()->subDays(30)->format('Y-m-d');
        $url = 'https://api.github.com/search/repositories?q=created:>'.$date.'&sort=stars&order=desc';

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);

        return json_decode($response->getBody());
    }

    public function getReposLanguagesDistinct($repos){
        return collect($repos)->map( function ($repo){
            return $repo->language;
        })->unique();
    }

    public function getReposListByLanguage($repos,$language){
        $filteredRepos = collect($repos)->where('language', $language);
        return $filteredRepos->map( function ($filteredRepo){
            return $filteredRepo->full_name;
        });
    }

    public function main(){
        $repos = $this->getData()->items;
        $languages = $this->getReposLanguagesDistinct($repos);
        $results = collect([]);
        foreach($languages as $language){
            $results->push([
                'language' => $language,
                'reposList' => $this->getReposListByLanguage($repos, $language),
            ]);
        };
        return view('main', [
            'results' => $results
        ]);
    }

}
