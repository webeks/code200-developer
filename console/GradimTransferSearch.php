<?php namespace Code200\Developer\Console;

use Code200\Solr\Models\SearchQuery;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use October\Rain\Database\ModelException;
use PDO;

class GradimTransferSearch extends Command
{

    const OLD_DB = "gradim_migration";
    const OLD_DB_USER = "root";
    const OLD_DB_PASS = "";

    /**
     * @var string The console command name.
     */
    protected $name = 'gradim:transfer-search';

    /**
     * @var string The console command description.
     */
    protected $description = 'Transfers search queries from old Joomla to OctoberCMS';

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        //connect to odl db connection
        $pdo = new PDO(
            'mysql:host=localhost;dbname=' . self::OLD_DB . ';charset=utf8',
            self::OLD_DB_USER,
            self::OLD_DB_PASS
        );

        $sQueries = $pdo->query("select * from jos_core_log_searches");
        foreach ($sQueries as $query) {
            try {

                $newQuery = new SearchQuery();
                $this->info("Importing query: " . $query['search_term']);

                $newQuery->search_query = strtolower($query['search_term']) ;
                $newQuery->slug = str_slug($query['search_term'], '-');
                $newQuery->count = $query['hits'];
                $newQuery->save();


            } catch (ModelException $e) {
                $this->error($e->getMessage());
                $this->error($e->getTraceAsString());
                if ($e->getMessage() == 'The query has already been taken.') {
                    $this->error("The query has already been taken.");
                }
            } catch (QueryException $f) {
                $this->error($f->getTraceAsString());
            }
        }


        $googleQueries = $this->searchFromGoogleWebmasters();
        foreach ($googleQueries as $googleQuery) {
            $newQuery = new SearchQuery();
            $result = SearchQuery::where("search_query", "=", strtolower($googleQuery[0]))->get()->first();
            $fakeSearchCount = $googleQuery[2];

            if($result) {
                $result->count = $result->count + $fakeSearchCount;
                $result->save();
            } else {
                $newQuery->search_query = strtolower($googleQuery[0]);
                $newQuery->slug = str_slug($googleQuery[0], '-');

                $newQuery->count = $fakeSearchCount;
                $newQuery->save();
            }
        }
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }





    protected function searchFromGoogleWebmasters() {
        $csv = str_getcsv("Queries,Clicks,Impressions,CTR,Position
strojne inštalacije,22,160,13.75%,3.0
izolacija strehe,21,244,8.61%,3.4
varjenje,18,363,4.96%,5.1
nadomestna gradnja,17,128,13.28%,4.2
zvočna izolacija,16,259,6.18%,7.8
nezahtevni objekti 2016,14,92,15.22%,1.5
elektroobločno varjenje,13,31,41.94%,1.3
gradim,13,83,15.66%,2.5
mig varjenje,12,105,11.43%,2.6
hidroizolacija,10,228,4.39%,5.7
projekt za pridobitev gradbenega dovoljenja,10,22,45.45%,4.2
toplotna prehodnost,10,104,9.62%,2.7
rušenje objekta,10,32,31.25%,1.9
mig mag varjenje,10,49,20.41%,2.0
postopki varjenja,10,31,32.26%,1.2
barve v stanovanju,10,61,16.39%,3.4
samooskrba z električno energijo,9,66,13.64%,4.6
tig varjenje,9,209,4.31%,4.8
lepljenje gips plošč na steno,9,44,20.45%,5.5
kletno stanovanje,9,20,45%,2.3
ravna streha,9,100,9%,3.4
vrste elektrod,8,30,26.67%,3.1
lesena fasada,8,111,7.21%,7.0
varjenje z elektrodo,8,39,20.51%,4.1
konstrukcija strehe,8,54,14.81%,1.8
zvočna izolacija tal,8,58,13.79%,6.4
enostavni objekti 2016,7,22,31.82%,1.8
varjenje mig,7,29,24.14%,2.0
izolacija mansarde,7,89,7.87%,6.6
kovinski vložki za knauf votle stene,7,28,25%,5.5
lepljenje lesa,7,35,20%,4.3
idejna zasnova,7,31,22.58%,2.4
samooskrba z elektriko,6,30,20%,1.0
izolacija podstrešja,6,328,1.83%,5.1
vrtanje,6,95,6.32%,5.1
mig mag,6,60,10%,5.2
projektna dokumentacija,6,77,7.79%,5.4
mag varjenje,6,59,10.17%,2.5
toplotna izolacija strehe,6,29,20.69%,3.3
cena električnega priključka novogradnja,6,10,60%,1.1
podstrešje,6,124,4.84%,4.3
strelovod,5,72,6.94%,5.1
strojne instalacije,5,50,10%,3.0
net metering forum,5,17,29.41%,4.9
nadomestna gradnja 2016,5,24,20.83%,3.0
suhomontažna gradnja,5,37,13.51%,6.8
obnova strehe,5,38,13.16%,2.0
priklop elektrike novogradnja,5,23,21.74%,2.0
priklop elektrike,5,80,6.25%,7.2
barve za stanovanje,4,15,26.67%,2.4
mig mag tig,4,14,28.57%,15
ravne strehe,4,55,7.27%,6.1
varjenje tig,4,36,11.11%,3.6
lesena tla,4,40,10%,4.6
sončna elektrarna za samooskrbo,4,76,5.26%,5.6
pomen barv v stanovanju,4,10,40%,1.6
lesene fasade,4,152,2.63%,9.4
izolacija podstrešja tla,4,66,6.06%,8.4
toplotna prehodnost oken,4,19,21.05%,3.0
sončne elektrarne za samooskrbo,4,27,14.81%,5.8
tla v dnevni sobi,4,9,44.44%,4.7
elektrode za varjenje,4,49,8.16%,6.1
strelovod cena,4,19,21.05%,3.3
elektro obločno varjenje,4,21,19.05%,7.9
izvedba talnega gretja,4,15,26.67%,4.7
varjenje co2,4,20,20%,4.6
izvedba ravne strehe,4,31,12.9%,6.4
gradbeno dovoljenje za prizidek,3,49,6.12%,5.5
prizidek k hiši,3,49,6.12%,7.9
varjenje z elektrodami,3,29,10.34%,3.4
lastnosti lesa,3,89,3.37%,8.3
oblanje lesa,3,18,16.67%,4.7
pogodba med izvajalcem in naročnikom,3,20,15%,6.8
nezahtevni objekti 2015,3,30,10%,2.2
obločno varjenje,3,47,6.38%,8.0
prizidek brez gradbenega dovoljenja,3,60,5%,8.1
izolacija strehe med špirovci,3,31,9.68%,5.8
kako optično povečati prostor,3,14,21.43%,3.9
manj zahtevni objekti,3,24,12.5%,2.5
prizidek k stanovanjski hiši,3,22,13.64%,5.4
vrste varjenja,3,30,10%,2.1
talno ogrevanje izvedba,3,18,16.67%,8.3
lesen pod,3,18,16.67%,1.9
talno gretje izvedba,3,74,4.05%,6.7
maksimalna temperatura vode v talnem gretju,3,38,7.89%,9.7
cena priključka za elektriko,3,12,25%,5.3
hiša ali stanovanje,3,10,30%,4.4
nezahtevni objekti,3,76,3.95%,7.4
manj zahteven objekt,3,6,50%,2.2
pomen barv,3,47,6.38%,9.0
samooskrba z elektriko cena,3,8,37.5%,1.1
gradnja prizidka,3,22,13.64%,3.4
mini kopalnice,3,22,13.64%,9.0
električni priključek cena,2,23,8.7%,6.6
kako narediti savno,2,13,15.38%,8.2
izolacija neogrevanega podstrešja,2,6,33.33%,9.0
izolacija ostrešja,2,18,11.11%,4.6
pvc talne obloge za kopalnico,2,10,20%,7.6
izolacija podstrešja in mansarde,2,33,6.06%,6.8
police po meri,2,15,13.33%,3.5
toplotna izolacija podstrešja,2,48,4.17%,5.5
nosilnost lesa,2,15,13.33%,5.6
talno gretje polaganje cevi,2,16,12.5%,7.8
zvočna izolacija sten,2,18,11.11%,11
keram trade,2,16,12.5%,4.7
stenske obloge za kopalnico,2,22,9.09%,7.2
barva sten,2,8,25%,5.0
varjenje mig mag,2,13,15.38%,1.9
nezahtevni objekt odmik od meje,2,16,12.5%,10
centralni sesalni sistem,2,32,6.25%,13
izolacija pohodnega podstrešja,2,25,8%,6.9
kovice za kovičenje,2,37,5.41%,4.9
najboljša tla za kuhinjo,2,8,25%,10
izolacija tal v kleti,2,16,12.5%,4.9
tehnični pregled objekta,2,12,16.67%,2.6
barve sten v stanovanju,2,20,10%,5.9
protokol talnega gretja,2,11,18.18%,5.7
paroprepustnost,2,14,14.29%,1.0
prenova stanovanja v bloku,2,30,6.67%,8.7
revija gradim,2,2,100%,1.0
načrtovanje kopalnice,2,20,10%,9.3
vrtanje kovin,2,7,28.57%,3.3
enostavni objekti,2,71,2.82%,8.3
prizidki k hiši,2,12,16.67%,9.0
polaganje hidroizolacije,2,13,15.38%,3.5
sončne celice za elektriko,2,4,50%,13
soliter na steni,2,12,16.67%,5.8
enostavni objekti velikost,2,25,8%,6.8
plesen v stanovanju sanacija,2,10,20%,2.1
vložki za knauf plošče,2,20,10%,7.3
najvišja temperatura talnega gretja,2,19,10.53%,6.1
izolacija tal podstrešja,2,14,14.29%,9.2
enostaven objekt,2,29,6.9%,4.2
paropropustnost,2,2,100%,1.0
parna zapora da ali ne,2,8,25%,3.0
zvočna izolacija sobe,2,22,9.09%,9.4
zidna plesen sanacija,2,3,66.67%,1.0
strelovodi,2,26,7.69%,4.6
najboljša zaščita lesa zunaj,2,24,8.33%,2.7
kovičenje postopek,2,9,22.22%,2.2
domača delavnica,2,17,11.76%,2.2
cena estriha za talno gretje,2,39,5.13%,8.3
kombinacija barv za stene,2,32,6.25%,9.7
zvočna izolacija notranjih sten,2,49,4.08%,8.5
izolacija za streho,2,20,10%,5.4
zvočna izolacija sten v stanovanju,2,16,12.5%,5.0
parna zapora,2,117,1.71%,7.7
vlaga na podstrešju,2,9,22.22%,7.8
rastlinjak naredi sam,1,12,8.33%,11
izvijač,1,80,1.25%,8.8
kaj je pm10,1,5,20%,6.6
pravilnik o gradbiščih,1,40,2.5%,4.8
izolacija kleti v zemlji,1,28,3.57%,8.2
zaščita lesa,1,46,2.17%,6.4
nizkoenergijska hiša,1,34,2.94%,5.8
lepilo za gips plošče,1,7,14.29%,9.9
solarne celice naredi sam,1,2,50%,21
lesena fasada izvedba,1,12,8.33%,6.8
sončni kolektorji ali toplotna črpalka,1,8,12.5%,6.0
majhne kopalnice slike,1,23,4.35%,9.0
sušenje estriha z talnim ogrevanjem,1,7,14.29%,4.6
orodje za delavnice,1,49,2.04%,15
varilni aparat na polnjeno žico,1,4,25%,18
kopalnice sam,1,47,2.13%,8.4
impregnacija keramičnih ploščic,1,3,33.33%,7.0
tig varjenje tečaj,1,2,50%,13
gradimo,1,38,2.63%,4.2
izolacija tal na podstrešju,1,32,3.12%,8.3
enostavni objekt dimenzije,1,30,3.33%,6.5
barve sob,1,16,6.25%,10
zidni vložki za knauf,1,1,100%,10
vpliv barv v prostoru,1,2,50%,3.0
katere elektrode za varjenje,1,27,3.7%,3.6
barvne kombinacije,1,19,5.26%,10
sončne celice cena,1,20,5%,8.2
kako sam urediti okolico hiše,1,10,10%,8.2
obrnjena ravna streha,1,14,7.14%,5.7
turkizna barva za stene,1,26,3.85%,5.2
razredčilo,1,19,5.26%,7.1
zaključne letve za kuhinjski pult,1,4,25%,21
prenova stanovanj,1,6,16.67%,9.5
zavese za okna,1,8,12.5%,44
nezahtevni objekt,1,25,4%,6.6
osvetljenost prostorov,1,8,12.5%,8.8
parna zapora folija,1,8,12.5%,8.5
trdnost lesa,1,27,3.7%,6.3
varjenje bakra,1,4,25%,10
nizkoenergijske hiše,1,37,2.7%,5.9
filter dimnih plinov,1,12,8.33%,9.0
gradnja hiše na ključ,1,8,12.5%,26
zahtevni objekti,1,9,11.11%,3.3
lončene peči,1,10,10%,17
pomožno napajanje ob izpadu elektrike,1,13,7.69%,6.5
filter za dimnik,1,6,16.67%,5.3
plinsko varjenje,1,1,100%,19
talne obloge v kuhinji,1,5,20%,26
ekološka gradnja,1,13,7.69%,6.6
kako prenoviti staro hišo,1,16,6.25%,9.1
ureditev kletnih prostorov,1,9,11.11%,6.7
renovacija stanovanja,1,4,25%,15
izolacija strehe z zunanje strani,1,4,25%,17
obrnjena streha,1,11,9.09%,11
plin za varjenje,1,8,12.5%,9.1
kovičenje,1,26,3.85%,4.7
neopor,1,13,7.69%,6.8
gradbeni načrti,1,6,16.67%,4.3
izolacija podstrešnih tal,1,10,10%,7.0
postopek pridobitve gradbenega dovoljenja,1,1,100%,37
višina stola,1,10,10%,2.3
kako variti z co2,1,11,9.09%,6.4
zavarovanje toplotne črpalke,1,1,100%,7.0
zvočna izolacija stene,1,7,14.29%,9.3
cena talnega ogrevanja,1,1,100%,32
izvedba strešne konstrukcije,1,13,7.69%,6.4
stenske barve za kuhinjo,1,2,50%,18
knauf vložki,1,8,12.5%,5.9
idealna temperatura talnega gretja,1,7,14.29%,8.9
lesene strehe,1,1,100%,8.0
spone za širinsko lepljenje lesa,1,8,12.5%,7.0
talne keramične ploščice,1,8,12.5%,39
cena gradbenega dovoljenja za prizidek,1,10,10%,8.7
izolacija stropa proti podstrešju,1,29,3.45%,6.2
co2 varjenje,1,22,4.55%,5.1
zidanje dimnika naredi sam,1,5,20%,7.8
taksa za gradbeno dovoljenje,1,4,25%,2.0
spona za širinsko lepljenje,1,5,20%,6.6
talno gretje temperatura vode,1,13,7.69%,9.3
cena gradnje kleti,1,6,16.67%,8.5
solarne celice cena,1,4,25%,9.5
investitor,1,18,5.56%,5.8
leseni stropniki,1,8,12.5%,6.5
načrt organizacije gradbišča,1,17,5.88%,4.2
deskanje strehe,1,13,7.69%,4.6
susenje estriha,1,3,33.33%,6.3
pozor gradbišče,1,2,50%,11
talne obloge za talno gretje,1,9,11.11%,6.0
prenova strehe,1,29,3.45%,5.1
pzi dokumentacija,1,25,4%,8.2
oblič,1,63,1.59%,23
sredstvo proti zidni plesni,1,23,4.35%,9.0
zvočna izolacija tal cena,1,5,20%,9.2
izdelava strelovoda,1,8,12.5%,3.0
kako odzračiti talno gretje,1,33,3.03%,7.4
zidna plesen odstranjevanje,1,12,8.33%,11
kombinacija barv,1,28,3.57%,10
zaščita lesa zunaj,1,7,14.29%,2.6
cena prizidka k hiši,1,2,50%,11
pop kovice,1,5,20%,5.2
malta za omet razmerje,1,2,50%,23
vrtanje v kovino,1,5,20%,1.0
talno gretje da ali ne,1,8,12.5%,7.6
gradnja nizkoenergijske hiše,1,5,20%,1.4
gradbeno dovoljenje za enostaven objekt,1,5,20%,8.0
ravna streha cena,1,14,7.14%,5.4
lepljenje mavčnih plošč,1,11,9.09%,6.5
izolacija strehe nad špirovci,1,30,3.33%,8.0
tople barve v stanovanju,1,11,9.09%,5.4
montažna gradnja,1,37,2.7%,6.3
tig mig mag,1,3,33.33%,13
vodovodna inštalacija,1,16,6.25%,7.1
rušenje objektov,1,12,8.33%,7.0
gradnja in obnova,1,8,12.5%,8.3
pop neti,1,13,7.69%,5.8
podiranje stare hiše,1,1,100%,12
naredi sam,1,7,14.29%,11
velikost kopalnice,1,10,10%,5.4
izolacija sten od znotraj,1,5,20%,30
primeren čas za sečnjo,1,1,100%,13
lesene police po meri,1,9,11.11%,5.2
projekt izvedenih del,1,22,4.55%,2.5
ravne strehe+cena,1,17,5.88%,6.3
pm10 filter,1,1,100%,2.0
postopek nakupa stanovanja,1,1,100%,14
elektro varjenje,1,20,5%,3.9
obnova stare hiše cena,1,7,14.29%,9.9
hrastov les,1,7,14.29%,30
električni priključek,1,13,7.69%,3.1
strešna izolacija,1,29,3.45%,8.5
prenos toplote in snovi,1,5,20%,36
gradbeni nadzor,1,5,20%,10
vodovodna instalacija,1,9,11.11%,8.2
barvni krog komplementarne barve,1,19,5.26%,9.3
gradbišče,1,17,5.88%,3.5
prizidek k obstoječi hiši,1,17,5.88%,7.9
strelovod da ali ne,1,1,100%,3.0
izolacija strehe z notranje strani,1,32,3.12%,8.0
leasing nepremičnin,1,6,16.67%,18
gradbena dokumentacija,1,13,7.69%,3.1
navodila za obratovanje in vzdrževanje,1,11,9.09%,6.8
parket za talno gretje cena,1,2,50%,26
ogrevanje sanitarne vode s sončno energijo,1,12,8.33%,9.9
nezahtevni in enostavni objekti,1,11,9.09%,6.5
gradbeno dovoljenje za rušitev cena,1,27,3.7%,6.5
začetek gradnje hiše,1,3,33.33%,16
rezalna hitrost formula,1,5,20%,11
rušenje hiše,1,5,20%,2.0
rusenje objekta,1,1,100%,2.0
parna zapora ali ovira,1,4,25%,5.0
udobna sedežna garnitura,1,19,5.26%,12
zvočna izolacija stropa cena,1,5,20%,9.4
mig/mag,1,3,33.33%,6.0
projekt za gradbeno dovoljenje,1,4,25%,4.0
w/m2k,1,16,6.25%,2.6
mag varjenje wikipedija,1,12,8.33%,4.8
prizidek,1,48,2.08%,7.2
parna ovira,1,36,2.78%,7.0
frčada v prostoru,1,2,50%,3.0
izolacija ravne strehe,1,20,5%,7.4
pid dokumentacija,1,26,3.85%,5.2
osvetlitev prostora,1,12,8.33%,7.6
zakovice za kovino,1,11,9.09%,8.2
lončene peči cena,1,1,100%,26
protihrupna izolacija,1,2,50%,6.5
alu parna zapora,1,4,25%,9.5
filter za dimnik cena,1,4,25%,6.0
talno ogrevanje temperatura vode,1,4,25%,10
odtočna cev za wc školjko,1,2,50%,14
izolacija stropa v kleti,1,3,33.33%,18
maja pisanec,1,1,100%,24
talno gretje ali radiatorji,1,29,3.45%,9.0
kako odstraniti zidno plesen,1,1,100%,18
strelovod cenik,1,13,7.69%,6.8
uredba o razvrščanju objektov glede na zahtevnost gradnje,1,95,1.05%,9.1
lesene police,0,6,0%,32
najstniška soba,0,4,0%,2.0
objekt definicija,0,1,0%,47
ploščice za talno gretje,0,1,0%,10
kako sestaviti zapisnik,0,1,0%,7.0
skrite napake,0,3,0%,34
pečarski šamot,0,2,0%,45
varjenje aluminija po tig postopku,0,12,0%,6.0
sečnja lesa,0,1,0%,58
gradbeno dovoljenje,0,4,0%,35
poceni orodje,0,3,0%,39
lesena streha,0,3,0%,34
padec odtočnih cevi,0,2,0%,12
poraba energije za ogrevanje hiše,0,1,0%,13
polaganje ploščic v kopalnici,0,4,0%,34
gradbena elektrika,0,6,0%,9.5
načrtovanje kuhinje,0,4,0%,51
lazurni namaz,0,1,0%,24
lizing za hišo,0,1,0%,10
barve jupol,0,5,0%,24
izolacija stropa,0,5,0%,49
gres ploščice,0,10,0%,10
ureditev male kopalnice,0,1,0%,17
hiša toplote,0,6,0%,8.7
vrtanje lukenj,0,3,0%,30
zvočna izolacija stropa v bloku,0,9,0%,8.1
lesene ograje za vrt,0,7,0%,32
križni odvijač,0,1,0%,7.0
toplotna izolacija cevi,0,1,0%,72
umivalnik za wc,0,1,0%,41
nadzornik gradnje,0,1,0%,16
izračun moči toplotne črpalke,0,1,0%,43
torx velikosti,0,2,0%,3.0
izolacija stene,0,1,0%,29
barva modra,0,3,0%,8.7
cena priklopa elektrike,0,18,0%,8.9
okoljska marjetica,0,2,0%,12
prevajanje toplote,0,5,0%,27
toplotna črpalka,0,2,0%,120
nadomestna gradnja cena,0,5,0%,10
barvne stene,0,1,0%,26
posek in spravilo lesa,0,1,0%,130
posoda za vrtni ogenj,0,1,0%,28
svedra,0,6,0%,6.2
obtočna črpalka za centralno ogrevanje,0,1,0%,53
gradbeno dovoljenje za nezahtevni objekt cena,0,4,0%,21
fotelji počivalniki,0,1,0%,69
notranja izolacija sten,0,1,0%,66
oprema za varjenje,0,5,0%,21
torx ključ,0,2,0%,26
premazi za les,0,11,0%,26
protokol zagona talnega gretja,0,1,0%,4.0
gradnja enostavnih objektov,0,17,0%,4.9
odtenki barv,0,3,0%,17
idejni projekt,0,24,0%,5.5
vložki za knauf,0,11,0%,8.7
majhna kopalnica ideje forum,0,3,0%,12
najcenejše montažne hiše,0,1,0%,65
omrežnina za priključno moč,0,3,0%,14
net metering v praksi,0,1,0%,7.0
gradbeno dovoljenje za rekonstrukcijo objekta,0,4,0%,8.5
polaganje ploščic je lahko zelo preprosto,0,1,0%,44
lido počivalniki cena,0,1,0%,20
notranja izolacija zunanjih sten,0,1,0%,33
udarni vrtalnik,0,2,0%,36
varilna žica,0,5,0%,42
pod svojo streho kopalnica,0,3,0%,32
zelo majhna kopalnica,0,3,0%,6.3
prijava gradnje,0,1,0%,5.0
prenova sedežne garniture cena,0,1,0%,27
lesene stenske obloge,0,1,0%,86
rastlinjak,0,5,0%,71
prizidek vhod,0,1,0%,4.0
gradnja vinske kleti,0,1,0%,41
prerez strehe,0,3,0%,8.3
gradnja garaže brez gradbenega dovoljenja,0,5,0%,7.8
stropne konstrukcije,0,1,0%,56
strojne,0,1,0%,7.0
klasifikacija objektov glede na zahtevnost,0,16,0%,6.0
graditi,0,1,0%,40
revija hiše,0,1,0%,92
motor za kosilnico,0,1,0%,19
ramda kosilnice,0,2,0%,29
prenova stare hiše,0,1,0%,19
stola,0,2,0%,11
debelina estriha za talno gretje,0,4,0%,10
cev za sesalec,0,3,0%,40
стреха,0,1,0%,39
klasična hiša,0,7,0%,8.6
barve sten v dnevni sobi,0,2,0%,15
košnja,0,9,0%,34
ujemanje barv,0,2,0%,43
vrtne potke,0,1,0%,36
enokapnice,0,1,0%,85
negorljiva izolacija,0,2,0%,43
katero toplotno črpalko izbrati,0,5,0%,9.0
velikosti oken,0,1,0%,26
pasivna hiša cena,0,1,0%,82
tehniška dokumentacija,0,1,0%,29
prijava gradbišča,0,4,0%,37
zaključne letve za pult,0,1,0%,20
rjava fasada,0,1,0%,23
križni izvijač,0,7,0%,7.0
dimenzioniranje toplotne črpalke,0,1,0%,29
toti dca maribor,0,1,0%,68
stavbno pohištvo,0,3,0%,67
stanovati,0,1,0%,9.0
odtenki zelene barve,0,2,0%,10
leseni pod,0,26,0%,4.7
grča v lesu,0,11,0%,7.2
sončni kolektorji cenik,0,2,0%,45
moja hisa,0,2,0%,10
isover duplex,0,1,0%,7.0
talno gretje temperatura,0,1,0%,10
krušna peč gradnja,0,2,0%,40
bosanka plošča,0,1,0%,44
zidanje hiše,0,2,0%,65
ureditev vrta,0,7,0%,44
mig mag varenje,0,1,0%,5.0
rekonstrukcija objekta,0,8,0%,11
secnja in spravilo lesa,0,5,0%,120
drva za kurjavo hrvaška,0,1,0%,50
gradbiščna tabla,0,37,0%,6.9
prenova stanovanja,0,11,0%,17
barva spalnice,0,5,0%,18
kombinacija barv z modro,0,3,0%,14
varjenje brez co2,0,9,0%,9.0
zimski vrt gradbeno dovoljenje,0,2,0%,22
hladne in tople barve,0,9,0%,8.8
kako napisati ponudbo za storitev,0,5,0%,23
vrtne kosilnice akcija,0,2,0%,36
plesen v stanovanju,0,2,0%,19
varilna žica polnjena,0,1,0%,41
kopalnica oprema,0,1,0%,35
zvocna izolacija,0,23,0%,8.6
hidroizolacija hiše,0,1,0%,7.0
montažna hiša,0,4,0%,37
tople in hladne barve,0,22,0%,8.5
vlaga v kopalnici,0,1,0%,22
vložki za gips plošče,0,2,0%,10
bojler za toplotno črpalko,0,1,0%,26
plesen na steni,0,24,0%,9.9
tig mag mig,0,2,0%,17
traktorska kosilnica,0,1,0%,64
vezana plošča obi,0,4,0%,38
lončena peč,0,4,0%,20
ravna streha forum,0,5,0%,2.0
nosilne konstrukcije,0,101,0%,4.5
izolacija strehe cena,0,4,0%,28
postopek varjenja,0,3,0%,3.7
cena talnega gretja,0,6,0%,32
toplotna prevodnost lesa,0,3,0%,11
tipske hiše načrti,0,1,0%,96
ogrevanje vode,0,12,0%,7.3
odduh kanalizacije,0,2,0%,38
toplotna izolacija stropa v kleti,0,1,0%,14
katerega operaterja izbrati,0,1,0%,41
pravilnik o projektni dokumentaciji,0,3,0%,23
zidaki za ograjo,0,1,0%,41
slike kopalnic,0,3,0%,61
ploščice za tla,0,5,0%,10
spravilo lesa iz gozda,0,3,0%,140
barve dnevne sobe,0,1,0%,55
predračun vzorec,0,2,0%,27
net metering v sloveniji,0,4,0%,12
sekanje,0,2,0%,74
naredi sam ideje,0,1,0%,97
barve kuhinje,0,6,0%,28
ureditev okolice vikenda,0,1,0%,43
pleskanje sten vzorci,0,2,0%,23
slikopleskarstvo,0,1,0%,160
ploščice v kuhinji,0,1,0%,46
cvetnice,0,9,0%,6.8
šola varjenja,0,5,0%,9.6
wc školjka z bidejem,0,1,0%,31
gradnja pasivne hiše,0,2,0%,34
nizkoenergijske hiše cena,0,1,0%,14
elektrode za varjenje cena,0,4,0%,11
cena hiše na ključ,0,1,0%,66
odgovorni vodja del,0,2,0%,39
kopalnica v mansardi,0,1,0%,30
izolacija notranjih sten,0,2,0%,31
energijsko varčne hiše,0,2,0%,40
zakon o graditvi objektov 2016,0,8,0%,35
reklamacija računa primer,0,1,0%,30
prednosti in slabosti talnega gretja,0,8,0%,5.8
barva spalnice v stanovanju,0,1,0%,3.0
umivalnik za kopalnico,0,2,0%,58
lepljenje tapet,0,1,0%,81
kapilarni dvig,0,7,0%,35
gradnja nezahtevnih objektov,0,10,0%,6.6
barve za kuhinjo,0,8,0%,13
vodilna mapa primer,0,2,0%,18
steklenjak,0,2,0%,24
nakup nepremičnine postopek,0,2,0%,12
solarne elektrarne,0,1,0%,50
podiranje nosilne stene,0,3,0%,10
vodovodna inštalacija v kopalnici,0,4,0%,10
mansardno stanovanje,0,20,0%,37
gojenje zelenjave,0,4,0%,33
varjenje aluminija mig,0,4,0%,9.5
mag mig,0,2,0%,2.0
kitanje mavčnih plošč,0,1,0%,45
mesečni stroški stanovanja,0,10,0%,9.3
se les,0,2,0%,7.5
jakuzi,0,1,0%,36
ogrevanje s toplotno črpalko,0,3,0%,95
svetle in temne barve,0,2,0%,40
fotovoltaika forum,0,3,0%,16
skrita napaka,0,4,0%,27
izračun toplotne črpalke,0,4,0%,40
ponudniki montažnih hiš,0,1,0%,51
idejni,0,21,0%,7.7
sončni kolektor,0,1,0%,67
strojelom,0,1,0%,8.0
obnova hiše kje začeti,0,1,0%,28
temeljna barva za kovino,0,1,0%,47
priklop elektrike novogradnja cena,0,3,0%,3.7
urejanje dvorišča,0,1,0%,110
kombiniranje barv,0,4,0%,16
sončni kolektorji za elektriko,0,4,0%,20
lesene letve,0,8,0%,32
sušilnica lesa,0,1,0%,44
lesni črv,0,2,0%,40
enostavna gradnja,0,8,0%,6.0
gradnja hiše v lastni režiji,0,8,0%,21
najboljše toplotne črpalke,0,2,0%,61
kopalnice brez ploščic,0,1,0%,65
lesena tla v kopalnici,0,3,0%,34
mig varenje,0,1,0%,39
montažni prizidek k hiši,0,5,0%,9.2
sam mig,0,1,0%,51
nezahteven objekt,0,29,0%,4.8
zaključne letve za keramiko,0,1,0%,64
plesen na stenah,0,1,0%,20
pohodna streha,0,1,0%,4.0
lesni spoji,0,1,0%,11
modra kuhinja,0,1,0%,25
enostavni objekt,0,38,0%,7.4
hidroizolacija tal,0,10,0%,7.7
priročnik za gradnjo hiše v lastni režiji,0,1,0%,15
gradnja hiše stroški,0,2,0%,45
enostanovanjske hiše,0,1,0%,88
servis oljnih gorilcev ljubljana,0,1,0%,110
debelina izolacije,0,1,0%,34
definicija objekta,0,1,0%,7.0
tig mig,0,2,0%,3.5
izolacija za talno gretje,0,6,0%,11
slamnata streha,0,32,0%,6.8
odtenki modre barve,0,13,0%,8.9
polnjena žica za varjenje brez plina,0,1,0%,12
toplotne črpalke za ogrevanje hiše,0,1,0%,70
prodaja keramičnih ploščic,0,1,0%,66
izjava o skladnosti izvedenih del,0,1,0%,9.0
gradnja brez gradbenega dovoljenja,0,15,0%,9.1
sanacija dimnika cena,0,1,0%,29
poceni stoli,0,1,0%,49
uporabno dovoljenje za starejše objekte,0,1,0%,24
dom oprema,0,1,0%,74
zračniki za fasado,0,3,0%,25
stare kleti,0,7,0%,7.0
zunanje keramične ploščice,0,1,0%,66
leasing hiša,0,1,0%,13
rezalna hitrost,0,17,0%,9.0
kopalnica mansarda,0,1,0%,32
stojalo za vrtalni stroj,0,25,0%,31
ročni oblič,0,1,0%,29
sušenje estrihov,0,3,0%,8.0
stena za orodje,0,4,0%,14
frčade,0,4,0%,10
kako pobarvati kovino?,0,1,0%,29
proizvajalci oken,0,5,0%,34
barvni krog,0,9,0%,16
stroji za kovino,0,2,0%,49
pod svojo streho ogrevanje,0,2,0%,45
temperatura tal,0,4,0%,24
talne obloge linolej,0,2,0%,43
gradbeno dovoljenje za nezahteven objekt,0,10,0%,8.6
ugodnejša elektrika 17,0,2,0%,18
vgradna omara naredi sam,0,2,0%,26
premaz za fuge,0,1,0%,10
katera okna izbrati,0,4,0%,48
vijolična barva za stene,0,3,0%,5.7
izolacija brunarice,0,1,0%,82
višina oken od tal,0,18,0%,5.4
debelina estriha,0,2,0%,19
naredi sam vrt,0,1,0%,33
povrtalo za kovino,0,1,0%,19
toplotna izolacija stropa,0,6,0%,24
toplotna črpalka ogrevanje,0,1,0%,130
izolacija mansarde cena,0,1,0%,38
cevi za centralno ogrevanje,0,4,0%,44
ograje vrtne,0,1,0%,89
hiše lumar,0,10,0%,46
majhna kopalnica,0,5,0%,13
moderna kopalnica brez ploščic,0,1,0%,30
barva kuhinje stene,0,2,0%,10
bojler toplotna črpalka,0,4,0%,30
priklop na električno omrežje cena,0,9,0%,4.9
gradnja v lastni režiji,0,4,0%,22
obtočne črpalke za centralno ogrevanje,0,1,0%,50
motorne kosilnice,0,18,0%,22
mig mag varilni aparat,0,7,0%,32
električni vijačnik,0,1,0%,35
pasivna gradnja,0,1,0%,70
olje za motor,0,6,0%,79
leseni pod sibirskega macesna,0,1,0%,57
tla v stanovanju,0,1,0%,7.0
varčna raba energije,0,2,0%,49
tlakovana dvorišča,0,1,0%,13
zaščita za les,0,5,0%,10
ureditev stanovanja,0,3,0%,34
dozidava hiše,0,6,0%,4.8
obnova kopalnice,0,4,0%,41
odstranjevanje ploščic,0,1,0%,10
hss jeklo,0,4,0%,21
ravne frčade,0,4,0%,4.0
bankirai,0,3,0%,7.3
predkupna pravica,0,3,0%,31
sredstvo proti plesni,0,2,0%,11
hidroizolacija ravne strehe,0,10,0%,11
sečnja,0,1,0%,84
centralna na elektriko,0,1,0%,62
odtoki v kopalnici,0,1,0%,40
barva sten v spalnici,0,1,0%,9.0
melanholiki,0,2,0%,89
sončne elektrarne forum,0,4,0%,14
barve prostorov,0,5,0%,4.6
kaj je elektrika,0,1,0%,47
lido sedežne,0,2,0%,11
talni radiatorji,0,1,0%,40
folija za rastlinjak,0,2,0%,38
pastelne barve za stene,0,5,0%,11
plošče za talno gretje,0,1,0%,40
ideje za mansardno stanovanje,0,1,0%,36
marles montažne hiše,0,1,0%,82
kombinacije barv v stanovanju,0,1,0%,14
terciarne barve,0,29,0%,9.0
izolacija za podstrešje,0,7,0%,6.6
boris valenčič,0,1,0%,49
ovira,0,6,0%,9.7
sredstvo za odstranjevanje plesni,0,1,0%,23
kosilnica,0,1,0%,48
pritisk v talnem gretju,0,3,0%,9.0
prodaja ploščic,0,1,0%,64
parket za talno ogrevanje,0,6,0%,28
objekti brez gradbenega dovoljenja,0,1,0%,9.0
trdnost,0,1,0%,40
tekstilne talne obloge,0,1,0%,70
renoviranje kopalnice,0,2,0%,32
keramične ploščice ljubljana,0,3,0%,43
sta je streha,0,12,0%,5.0
vlaga v zidu,0,2,0%,16
slovenski čebelnjak,0,1,0%,25
rumena barva pomen,0,2,0%,16
polaganje hidroizolacije youtube,0,2,0%,4.5
strešna konstrukcija,0,3,0%,9.7
barva za keramične ploščice,0,2,0%,46
hidroizolacija kleti,0,20,0%,7.5
skrite napake pri nakupu nepremičnine,0,13,0%,8.9
sedežna garnitura iz mikrotkanine,0,10,0%,13
izolacija kleti z notranje strani,0,20,0%,9.4
izolacija podstrešja cena,0,5,0%,33
lak za les na vodni osnovi,0,2,0%,19
uporabno dovoljenje,0,86,0%,7.1
zakaj parna zapora,0,8,0%,4.0
izbira,0,1,0%,40
izolacija zunanjih sten,0,2,0%,28
vrtne kosilnice,0,32,0%,34
rdeča barva pomen,0,2,0%,13
polnjena žica za varjenje,0,5,0%,26
nizkoenergijska hiša definicija,0,2,0%,10
lepilo za knauf,0,17,0%,7.7
net metering cena,0,7,0%,6.7
armature armal,0,2,0%,26
tabele za tehnični pregled objekta,0,6,0%,6.0
pod streho,0,6,0%,16
rezalna hitrost pri vrtanju,0,9,0%,4.0
darilna pogodba kmetijsko zemljišče,0,1,0%,76
sončni kolektorji za sanitarno vodo cena,0,2,0%,10
adaptacija starega stanovanja,0,3,0%,11
debelina tlaka za talno gretje,0,6,0%,9.5
barve v dnevni sobi,0,4,0%,17
reklamacija storitve,0,1,0%,19
pleskanje,0,1,0%,190
alpina kosilnice,0,1,0%,27
popravilo strehe,0,4,0%,83
osvetlitev,0,2,0%,19
obnova fasade cena,0,1,0%,120
napake pri varjenju,0,13,0%,7.2
strelo,0,6,0%,6.5
zunanje barve za les,0,6,0%,8.8
primerjava toplotnih črpalk,0,1,0%,92
kvalitetni svedri za kovino,0,14,0%,9.4
varcen,0,2,0%,8.0
tig mag,0,5,0%,22
hišni mojster,0,1,0%,59
toplotna izolacija hiše,0,1,0%,68
podiranje stene cena,0,1,0%,19
ljepljenje knaufa na strop,0,1,0%,14
servis kosilnic,0,2,0%,35
zvarjeno mesto,0,18,0%,9.1
uredba o vrstah objektov glede na zahtevnost,0,33,0%,8.6
izolacija betonske strehe,0,7,0%,9.1
sedežne garniture,0,1,0%,93
lesen pult,0,1,0%,27
zamenjava strehe cena,0,6,0%,42
reklamacija primer,0,1,0%,28
tloris kopalnice,0,1,0%,19
mikrotkanina,0,1,0%,37
plesen v kopalnici,0,1,0%,34
orodja za vrtanje,0,7,0%,9.6
savna doma,0,39,0%,15
kako zgraditi hišo,0,3,0%,56
nizkoenergijska gradnja,0,1,0%,6.0
proti vlagi v stanovanju,0,2,0%,21
zasnova,0,10,0%,6.0
montažne hiše najcenejše,0,1,0%,64
sveder,0,3,0%,12
zaključna letev za kuhinjski pult,0,1,0%,24
izolacija zunanje stene,0,1,0%,22
spone za širinsko lepljenje,0,7,0%,6.9
les po meri,0,1,0%,9.0
oprema kopalnice,0,4,0%,73
mehanske lastnosti lesa,0,15,0%,7.5
opečni tlakovci,0,1,0%,86
transformator toka,0,1,0%,44
savna v kopalnici,0,2,0%,6.0
polkna cena,0,5,0%,32
kovice neti,0,2,0%,16
ideje za urejanje okolice,0,2,0%,46
vrste projektne dokumentacije,0,2,0%,9.5
vlek dimnika izračun,0,1,0%,24
hrast les,0,1,0%,18
slepa kovica,0,1,0%,43
izolacija stene z notranje strani,0,1,0%,19
primerne barve za hodnik,0,2,0%,6.0
prenova kopalnic,0,2,0%,50
umivalniki za kopalnice,0,1,0%,74
sanacija strehe,0,30,0%,40
projekt za izvedbo,0,10,0%,8.1
izpad elektrike,0,2,0%,47
hidroizolacija kleti od znotraj,0,14,0%,8.6
mig mag wiki,0,5,0%,16
zasnova.com,0,5,0%,30
vgradnja strešnega okna,0,24,0%,27
višina kuhinjskega pulta,0,4,0%,11
naredi sam pohištvo,0,41,0%,44
izolacijske plošče za streho,0,8,0%,6.5
ponudba za delo primer,0,3,0%,52
izdelava kopalnice,0,1,0%,74
svetlobni jašek,0,1,0%,39
projektna dokumentacija primer,0,7,0%,14
zahtevnost objekta,0,2,0%,3.5
gradbeno dovoljenje za nezahtevne objekte,0,1,0%,9.0
lesna vlakna,0,1,0%,50
strešna okna,0,3,0%,61
toplotna,0,1,0%,79
izdaja gradbenega dovoljenja,0,1,0%,33
betonska ograja,0,3,0%,21
preveč olja v motorju,0,1,0%,48
barve sten v spalnici,0,5,0%,11
barve fasad,0,4,0%,42
sedežne garniture lido,0,5,0%,11
lepilo za les,0,19,0%,19
gres keramika,0,4,0%,16
vijolična barva,0,19,0%,11
delci pm10,0,1,0%,45
senčila velux trzin,0,7,0%,56
elektrode,0,5,0%,14
zvočna izolacija stene v bloku,0,4,0%,10
sušenje estriha,0,25,0%,8.6
gradnja vikenda,0,1,0%,34
naklon ravne strehe,0,1,0%,13
kuhinjsko pohištvo,0,1,0%,100
ureditev doma,0,3,0%,28
načini varjenja,0,3,0%,3.3
rabo hisa,0,1,0%,8.0
mig tig,0,2,0%,5.0
predkupna pravica občine,0,5,0%,9.0
zasaditev grede s trajnicami,0,1,0%,23
peči keramika,0,1,0%,40
zamenjava strehe,0,4,0%,17
čiščenje peči na drva,0,2,0%,25
barve za kopalnico,0,1,0%,51
modra barva,0,2,0%,11
izolacija notranje stene,0,1,0%,28
stroški gradnje hiše,0,1,0%,58
varilna žica co2,0,4,0%,9.8
barva proti zidni plesni,0,1,0%,10
knaufanje,0,3,0%,6.0
kaj je les,0,2,0%,10
hiša na ključ klasična gradnja,0,10,0%,9.2
enodružinske hiše,0,1,0%,64
kosilnice,0,13,0%,42
debelina izolacije v mansardi,0,8,0%,8.8
bukova hlodovina,0,1,0%,55
dimenzije kuhinjskih elementov,0,3,0%,45
sam kopalnice,0,50,0%,6.6
barvni krogi,0,3,0%,10
trdota lesa,0,2,0%,13
cena prenove kopalnice,0,3,0%,42
cena lesa za ostrešje,0,3,0%,38
hlod za predelavo na žagi,0,1,0%,41
obnova stanovanja,0,7,0%,13
notranja izolacija proti vlagi,0,13,0%,9.5
okovje za polkna,0,1,0%,68
spodnji del strehe,0,4,0%,3.0
zahteva za izdajo gradbenega dovoljenja za gradnjo nezahtevnega objekta,0,1,0%,23
detajl ravne strehe,0,1,0%,80
montažne hiše izkušnje,0,1,0%,73
kaj je leasing,0,2,0%,18
pvc cevi dimenzije,0,1,0%,55
pleskanje sten ideje,0,1,0%,60
proti plesni na zidu,0,3,0%,10
poceni obnova strehe,0,8,0%,9.4
ploščice za kopalnice,0,2,0%,39
toplotna črpalka z bojlerjem,0,2,0%,71
barvanje fug,0,1,0%,26
stenske barve za dnevno sobo,0,1,0%,11
ročni vrtalni stroj,0,3,0%,37
kopalniški pult cena,0,1,0%,36
mag mig tig,0,3,0%,33
ponudba za storitev primer,0,1,0%,59
ogrevanje na toplotno črpalko,0,2,0%,110
plesen v hiši,0,1,0%,33
hidroizolacija izotekt,0,1,0%,20
talno gretje ne greje,0,1,0%,10
barva proti plesni,0,1,0%,17
najboljši parket za talno gretje,0,2,0%,17
križni laser,0,4,0%,27
klinker ploščice,0,22,0%,45
frcade,0,3,0%,6.0
barv,0,1,0%,31
dokazilo o zanesljivosti objekta primer,0,1,0%,29
smrekove deske,0,2,0%,41
filter prašnih delcev,0,3,0%,30
pvc odtočne cevi,0,1,0%,45
toplotna prevodnost stekla,0,10,0%,9.0
barve spalnice,0,1,0%,12
kmečka lopa,0,19,0%,4.6
polaganje izolacije,0,4,0%,38
samostoječe ogledalo,0,1,0%,46
upogibna trdnost,0,1,0%,5.0
dimenzije wc školjke,0,1,0%,35
polaganje izolacije na podstrešju,0,3,0%,9.0
rekuperacija v stari hiši,0,1,0%,43
knauf plošče,0,2,0%,46
nove kopalnice,0,4,0%,14
barve stanovanja,0,15,0%,7.8
varilni aparat na žico brez plina,0,5,0%,19
vsebina projektne dokumentacije,0,2,0%,13
nezahtevni objekt na kmetijskem zemljišču,0,5,0%,6.6
barve fasade,0,1,0%,44
žagan les s kvadratnim prerezom,0,24,0%,8.5
za varjenje,0,7,0%,12
barve sten za dnevno sobo,0,1,0%,18
domača savna,0,62,0%,27
gradbeno dovoljenje za enostavne objekte,0,21,0%,6.7
pravilnik o projektni in tehnični dokumentaciji,0,9,0%,10
obnova hiše,0,4,0%,36
obnova sedežne cena,0,1,0%,27
strešne okna,0,11,0%,44
zvočne izolacije,0,3,0%,12
pohodne izolacijske plošče,0,2,0%,20
varilni aparati mig mag,0,7,0%,38
sestav,0,1,0%,9.0
stol višina,0,11,0%,6.7
leasing kredit,0,2,0%,19
lido d.o.o.,0,1,0%,30
mešanje barv vijolična,0,6,0%,9.7
sedežna garnitura za majhen prostor,0,15,0%,7.5
soglasje soseda za gradnjo,0,3,0%,41
marles hise,0,1,0%,43
lesene zaključne letve,0,2,0%,55
strešne konstrukcije,0,1,0%,69
izračun rezalne hitrosti,0,7,0%,7.7
dilatacija estriha,0,10,0%,3.9
zakon o graditvi objektov enostavni objekti,0,15,0%,5.5
peči na drva za centralno,0,1,0%,99
vodovodna napeljava v hiši,0,1,0%,67
stenske keramične ploščice,0,4,0%,23
vgradnja strešnih oken,0,23,0%,46
sestava ravne strehe,0,4,0%,10
folija za streho,0,5,0%,38
varjenje pločevine,0,10,0%,7.4
ploščice za hodnik,0,1,0%,45
enostavni objekti brez gradbenega dovoljenja,0,37,0%,8.3
projektna dokumentacija pravilnik,0,1,0%,10
argonsko varenje,0,1,0%,7.0
kredit ali leasing,0,5,0%,7.8
odgovorni vodja projekta,0,5,0%,9.8
mala kopalnica,0,6,0%,8.7
princip delovanja toplotne črpalke,0,1,0%,45
pomen vijolične barve,0,5,0%,9.4
ogrevanje kopalnice,0,2,0%,41
dimenzije stola,0,1,0%,3.0
velikost objekta brez gradbenega dovoljenja,0,29,0%,9.0
oprema za kopalnico,0,1,0%,82
montažna hiša marles,0,1,0%,45
leseno okno,0,1,0%,94
kopalnica sam,0,1,0%,8.0
lazura za les,0,3,0%,23
vgradni bazeni,0,1,0%,110
montažne hiše lumar,0,1,0%,23
komplementarne barve,0,4,0%,17
cevi za talno gretje,0,5,0%,37
prizidek k hiši cena,0,2,0%,11
steklo v kuhinji namesto ploščic,0,2,0%,46
ograja med sosedi,0,1,0%,10
pid projekt,0,7,0%,8.0
prizidek k hiši gradbeno dovoljenje,0,7,0%,6.1
sesalne cevi,0,5,0%,35
izolacija oken,0,4,0%,9.5
ureditev majhne kopalnice,0,4,0%,21
50 odtenkov teme obnova,0,1,0%,22
garderoba soba,0,1,0%,10
lesena konstrukcija strehe,0,8,0%,2.9
agepan,0,2,0%,43
vrste vijakov,0,28,0%,7.1
male kopalnice,0,2,0%,25
barvanje lesa,0,26,0%,8.7
pomožni objekt,0,3,0%,11
žica za varjenje,0,4,0%,17
prenos toplote,0,1,0%,160
elektro soglasje za priključitev,0,3,0%,9.3
vrtni traktor v okvari,0,4,0%,24
zelena barva pomen,0,4,0%,19
moderne kuhinje za male prostore,0,3,0%,56
kako odstraniti vlago in plesen v stanovanju,0,1,0%,17
nakup nepremičnine,0,32,0%,32
solarna elektrika,0,4,0%,10
sistemske plošče za talno gretje zelo ugodno,0,3,0%,19
moč luči,0,3,0%,44
izkop,0,60,0%,9.5
kaj je toplotna črpalka,0,1,0%,77
kombiniranje barv v stanovanju,0,3,0%,11
pokanje v ceveh centralne kurjave,0,3,0%,17
konstrukcija,0,1,0%,23
žagan les za ostrešje,0,2,0%,40
jub barve,0,1,0%,22
priklop agregata na hišno omrežje,0,1,0%,14
montaža toplotne črpalke,0,1,0%,85
stenske svetilke notranje,0,1,0%,83
lumar montažne hiše,0,2,0%,24
pogoji za gradbenega nadzornika,0,1,0%,7.0
najlepše kopalnice,0,2,0%,91
kuhinja mansarda,0,1,0%,18
katere cevi za talno gretje,0,16,0%,9.4
izolacija cevi centralne kurjave,0,1,0%,33
odprava vlage v zidu,0,2,0%,24
din en 1262,0,2,0%,57
okenski okvir,0,1,0%,30
mega kopalnica,0,2,0%,38
keramicne ploscice,0,1,0%,27
najboljše savne,0,1,0%,160
streha dvokapnica,0,4,0%,6.8
objektov,0,4,0%,9.0
urejanje okolice hiše slike,0,77,0%,26
sončni kolektorji forum,0,4,0%,28
delo reklamacije,0,9,0%,9.0
gradbeni nadzornik,0,2,0%,6.0
frčada,0,114,0%,7.7
toplotna črpalka priklop,0,1,0%,27
gradbeno dovoljenje za garažo,0,1,0%,14
strešno okno,0,50,0%,27
brušenje sten,0,11,0%,43
cena strehe forum,0,1,0%,24
odstranjevanje plesni,0,6,0%,18
vikingi zanimivosti,0,1,0%,92
sanacija lesenega stropa,0,8,0%,11
streha cena,0,2,0%,44
kuhinje za male prostore,0,4,0%,54
kopalnico ima,0,1,0%,41
vrste streh,0,3,0%,12
tople barve,0,55,0%,9.3", "\n");

        foreach($csv as &$Row) {
            $Row = str_getcsv($Row, ",", "");

        }
        return $csv;
    }

}
