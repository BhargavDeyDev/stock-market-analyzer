<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ═══════════════════════════════════════════════════════════════
//  HARDCODED SYMBOL MAP — zero DB dependency for market data
// ═══════════════════════════════════════════════════════════════
$SYM_MAP = [
  'AAPL'=>['name'=>'Apple Inc.','exchange'=>'NASDAQ','sector'=>'Technology','asset_type'=>'stock'],
  'MSFT'=>['name'=>'Microsoft Corporation','exchange'=>'NASDAQ','sector'=>'Technology','asset_type'=>'stock'],
  'GOOGL'=>['name'=>'Alphabet Inc.','exchange'=>'NASDAQ','sector'=>'Technology','asset_type'=>'stock'],
  'AMZN'=>['name'=>'Amazon.com Inc.','exchange'=>'NASDAQ','sector'=>'Consumer','asset_type'=>'stock'],
  'META'=>['name'=>'Meta Platforms','exchange'=>'NASDAQ','sector'=>'Technology','asset_type'=>'stock'],
  'TSLA'=>['name'=>'Tesla Inc.','exchange'=>'NASDAQ','sector'=>'Automotive','asset_type'=>'stock'],
  'NVDA'=>['name'=>'NVIDIA Corporation','exchange'=>'NASDAQ','sector'=>'Semiconductors','asset_type'=>'stock'],
  'NFLX'=>['name'=>'Netflix Inc.','exchange'=>'NASDAQ','sector'=>'Entertainment','asset_type'=>'stock'],
  'AMD'=>['name'=>'Advanced Micro Devices','exchange'=>'NASDAQ','sector'=>'Semiconductors','asset_type'=>'stock'],
  'INTC'=>['name'=>'Intel Corporation','exchange'=>'NASDAQ','sector'=>'Semiconductors','asset_type'=>'stock'],
  'ORCL'=>['name'=>'Oracle Corporation','exchange'=>'NYSE','sector'=>'Technology','asset_type'=>'stock'],
  'CRM'=>['name'=>'Salesforce Inc.','exchange'=>'NYSE','sector'=>'Software','asset_type'=>'stock'],
  'ADBE'=>['name'=>'Adobe Inc.','exchange'=>'NASDAQ','sector'=>'Software','asset_type'=>'stock'],
  'SNOW'=>['name'=>'Snowflake Inc.','exchange'=>'NYSE','sector'=>'Cloud','asset_type'=>'stock'],
  'UBER'=>['name'=>'Uber Technologies','exchange'=>'NYSE','sector'=>'Transport','asset_type'=>'stock'],
  'SHOP'=>['name'=>'Shopify Inc.','exchange'=>'NYSE','sector'=>'E-Commerce','asset_type'=>'stock'],
  'BABA'=>['name'=>'Alibaba Group','exchange'=>'NYSE','sector'=>'E-Commerce','asset_type'=>'stock'],
  'TSM'=>['name'=>'Taiwan Semiconductor','exchange'=>'NYSE','sector'=>'Semiconductors','asset_type'=>'stock'],
  'ASML'=>['name'=>'ASML Holding','exchange'=>'NASDAQ','sector'=>'Semiconductors','asset_type'=>'stock'],
  'JPM'=>['name'=>'JPMorgan Chase','exchange'=>'NYSE','sector'=>'Financials','asset_type'=>'stock'],
  'BAC'=>['name'=>'Bank of America','exchange'=>'NYSE','sector'=>'Financials','asset_type'=>'stock'],
  'GS'=>['name'=>'Goldman Sachs','exchange'=>'NYSE','sector'=>'Financials','asset_type'=>'stock'],
  'MS'=>['name'=>'Morgan Stanley','exchange'=>'NYSE','sector'=>'Financials','asset_type'=>'stock'],
  'V'=>['name'=>'Visa Inc.','exchange'=>'NYSE','sector'=>'Financials','asset_type'=>'stock'],
  'MA'=>['name'=>'Mastercard Inc.','exchange'=>'NYSE','sector'=>'Financials','asset_type'=>'stock'],
  'JNJ'=>['name'=>'Johnson & Johnson','exchange'=>'NYSE','sector'=>'Healthcare','asset_type'=>'stock'],
  'PFE'=>['name'=>'Pfizer Inc.','exchange'=>'NYSE','sector'=>'Healthcare','asset_type'=>'stock'],
  'UNH'=>['name'=>'UnitedHealth Group','exchange'=>'NYSE','sector'=>'Healthcare','asset_type'=>'stock'],
  'XOM'=>['name'=>'Exxon Mobil','exchange'=>'NYSE','sector'=>'Energy','asset_type'=>'stock'],
  'CVX'=>['name'=>'Chevron Corporation','exchange'=>'NYSE','sector'=>'Energy','asset_type'=>'stock'],
  'WMT'=>['name'=>'Walmart Inc.','exchange'=>'NYSE','sector'=>'Consumer','asset_type'=>'stock'],
  'COST'=>['name'=>'Costco Wholesale','exchange'=>'NASDAQ','sector'=>'Consumer','asset_type'=>'stock'],
  'PG'=>['name'=>'Procter & Gamble','exchange'=>'NYSE','sector'=>'Consumer','asset_type'=>'stock'],
  'KO'=>['name'=>'Coca-Cola Company','exchange'=>'NYSE','sector'=>'Consumer','asset_type'=>'stock'],
  'DIS'=>['name'=>'Walt Disney','exchange'=>'NYSE','sector'=>'Entertainment','asset_type'=>'stock'],
  'PYPL'=>['name'=>'PayPal Holdings','exchange'=>'NASDAQ','sector'=>'Fintech','asset_type'=>'stock'],
  'PLTR'=>['name'=>'Palantir Technologies','exchange'=>'NYSE','sector'=>'Technology','asset_type'=>'stock'],
  'COIN'=>['name'=>'Coinbase Global','exchange'=>'NASDAQ','sector'=>'Crypto','asset_type'=>'stock'],
  'MSTR'=>['name'=>'MicroStrategy Inc.','exchange'=>'NASDAQ','sector'=>'Technology','asset_type'=>'stock'],
  'SPY'=>['name'=>'SPDR S&P 500 ETF','exchange'=>'NYSE','sector'=>'Index ETF','asset_type'=>'etf'],
  'QQQ'=>['name'=>'Invesco QQQ ETF','exchange'=>'NASDAQ','sector'=>'Index ETF','asset_type'=>'etf'],
  'GLD'=>['name'=>'SPDR Gold Shares','exchange'=>'NYSE','sector'=>'Commodity ETF','asset_type'=>'etf'],
  'TLT'=>['name'=>'iShares 20yr Treasury','exchange'=>'NYSE','sector'=>'Bond ETF','asset_type'=>'etf'],
  'ARKK'=>['name'=>'ARK Innovation ETF','exchange'=>'NYSE','sector'=>'Active ETF','asset_type'=>'etf'],
  'VTI'=>['name'=>'Vanguard Total Market','exchange'=>'NYSE','sector'=>'Index ETF','asset_type'=>'etf'],
  'IWM'=>['name'=>'iShares Russell 2000','exchange'=>'NYSE','sector'=>'Index ETF','asset_type'=>'etf'],
  'BTC/USD'=>['name'=>'Bitcoin','exchange'=>'Crypto','sector'=>'Cryptocurrency','asset_type'=>'crypto'],
  'ETH/USD'=>['name'=>'Ethereum','exchange'=>'Crypto','sector'=>'Cryptocurrency','asset_type'=>'crypto'],
  'SOL/USD'=>['name'=>'Solana','exchange'=>'Crypto','sector'=>'Cryptocurrency','asset_type'=>'crypto'],
  'BNB/USD'=>['name'=>'BNB','exchange'=>'Crypto','sector'=>'Cryptocurrency','asset_type'=>'crypto'],
  'XRP/USD'=>['name'=>'XRP','exchange'=>'Crypto','sector'=>'Cryptocurrency','asset_type'=>'crypto'],
  'DOGE/USD'=>['name'=>'Dogecoin','exchange'=>'Crypto','sector'=>'Cryptocurrency','asset_type'=>'crypto'],
  'EUR/USD'=>['name'=>'Euro / US Dollar','exchange'=>'FX','sector'=>'Forex','asset_type'=>'forex'],
  'GBP/USD'=>['name'=>'British Pound / USD','exchange'=>'FX','sector'=>'Forex','asset_type'=>'forex'],
  'USD/JPY'=>['name'=>'US Dollar / Yen','exchange'=>'FX','sector'=>'Forex','asset_type'=>'forex'],
  'USD/INR'=>['name'=>'US Dollar / Rupee','exchange'=>'FX','sector'=>'Forex','asset_type'=>'forex'],
  'AUD/USD'=>['name'=>'Australian Dollar','exchange'=>'FX','sector'=>'Forex','asset_type'=>'forex'],
  'USD/CAD'=>['name'=>'US Dollar / CAD','exchange'=>'FX','sector'=>'Forex','asset_type'=>'forex'],
];

function symInfo($sym) {
    global $SYM_MAP;
    $sym = strtoupper(trim($sym));
    if (isset($SYM_MAP[$sym])) return array_merge(['symbol'=>$sym], $SYM_MAP[$sym]);
    try {
        $s = getDB()->prepare("SELECT symbol,name,exchange,sector,asset_type FROM symbols WHERE symbol=? AND is_active=1");
        $s->execute([$sym]); $r=$s->fetch(); if ($r) return $r;
    } catch(Exception $e) {}
    return null;
}

// ═══════════════════════════════════════════════════════════════
//  HTTP — cURL with file_get_contents fallback
// ═══════════════════════════════════════════════════════════════
function hGet($url, $to=8) {
    $ua='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML like Gecko) Chrome/122.0.0.0 Safari/537.36';
    if (function_exists('curl_init')) {
        $ch=curl_init($url);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>$to,CURLOPT_CONNECTTIMEOUT=>5,
            CURLOPT_USERAGENT=>$ua,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_FOLLOWLOCATION=>true,CURLOPT_MAXREDIRS=>3,CURLOPT_ENCODING=>'',
            CURLOPT_HTTPHEADER=>['Accept: application/json,text/html,*/*','Accept-Language: en-US,en;q=0.9']]);
        $r=curl_exec($ch); $e=curl_errno($ch); curl_close($ch);
        if ($r && !$e) return $r;
    }
    $ctx=stream_context_create(['http'=>['timeout'=>$to,'user_agent'=>$ua,'ignore_errors'=>true,
        'header'=>"Accept: application/json\r\nAccept-Language: en-US,en;q=0.9\r\n"],
        'ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]);
    return @file_get_contents($url,false,$ctx) ?: null;
}
function hJson($url,$to=8){$r=hGet($url,$to);if(!$r)return null;$d=json_decode($r,true);return is_array($d)?$d:null;}

// Yahoo symbol converter
function yS($s){return str_replace('/','_',str_replace('/','-',$s));}

// ═══════════════════════════════════════════════════════════════
//  DEBUG — /api.php?action=debug
// ═══════════════════════════════════════════════════════════════
if ($action==='debug') {
    $o=['php'=>PHP_VERSION,'curl'=>function_exists('curl_init')?'YES':'NO','server'=>$_SERVER['SERVER_NAME']??'?'];
    foreach(['yahoo_v7'=>'https://query1.finance.yahoo.com/v7/finance/quote?symbols=AAPL&fields=regularMarketPrice',
             'yahoo_v8'=>'https://query1.finance.yahoo.com/v8/finance/chart/AAPL?interval=1d&range=1d',
             'finnhub' =>'https://finnhub.io/api/v1/quote?symbol=AAPL&token='.FINNHUB_KEY,
             'twelvedata'=>'https://api.twelvedata.com/price?symbol=AAPL&apikey='.TWELVE_DATA_KEY] as $n=>$u){
        $t=microtime(true);$r=hGet($u,6);$ms=round((microtime(true)-$t)*1000);
        $o[$n]=['ok'=>!!$r&&strlen($r)>20,'ms'=>$ms,'preview'=>substr($r??'FAILED',0,80)];
    }
    try{getDB()->query("SELECT 1");$o['db']='ok';
        $c=getDB()->query("SELECT COUNT(*) FROM symbols")->fetchColumn();$o['symbols']=$c;}
    catch(Exception $e){$o['db']='FAIL: '.substr($e->getMessage(),0,60);}
    echo json_encode($o,JSON_PRETTY_PRINT);exit;
}

// ═══════════════════════════════════════════════════════════════
//  SEARCH SYMBOLS
// ═══════════════════════════════════════════════════════════════
if ($action==='search_symbols') {
    global $SYM_MAP;
    $q=strtolower(trim(clean($_GET['q']??'')));
    $out=[];
    foreach($SYM_MAP as $sym=>$info){
        if($q===''||$q==='%'||strpos(strtolower($sym),$q)!==false||strpos(strtolower($info['name']),$q)!==false)
            $out[]=array_merge(['symbol'=>$sym],$info);
        if(count($out)>=20&&$q!==''&&$q!=='%')break;
    }
    echo json_encode(array_values($out));exit;
}

// ═══════════════════════════════════════════════════════════════
//  QUOTE — PHP tries all sources; JS has its own fallback
// ═══════════════════════════════════════════════════════════════
if ($action==='quote') {
    $sym=strtoupper(trim(clean($_GET['sym']??'')));
    $info=symInfo($sym);
    if(!$info){echo json_encode(['error'=>"Unknown symbol: $sym"]);exit;}

    $h=(int)gmdate('H');$mi=(int)gmdate('i');$dow=(int)gmdate('N');
    $isOpen=($dow<=5&&(($h===13&&$mi>=30)||$h>13)&&$h<20);
    $ttl=$isOpen?60:300;

    // Cache check
    try{
        $cs=getDB()->prepare("SELECT data,updated_at FROM price_cache WHERE symbol=?");
        $cs->execute([$sym]);$cr=$cs->fetch();
        if($cr&&(time()-strtotime($cr['updated_at']))<$ttl){
            $cd=json_decode($cr['data'],true);$cd['_cached']=true;$cd['_symbol_info']=$info;
            echo json_encode($cd);exit;
        }
    }catch(Exception $e){}

    $ySym=yS($sym); $data=null;

    // Source 1: Yahoo v7 query1
    $r=hJson('https://query1.finance.yahoo.com/v7/finance/quote?symbols='.urlencode($ySym).'&fields=regularMarketPrice,regularMarketOpen,regularMarketDayHigh,regularMarketDayLow,regularMarketVolume,regularMarketPreviousClose,marketCap,trailingPE,shortName,regularMarketChangePercent',7);
    if($r&&!empty($r['quoteResponse']['result'][0]['regularMarketPrice'])){
        $q=$r['quoteResponse']['result'][0];$p=floatval($q['regularMarketPrice']);$o=floatval($q['regularMarketOpen']??$p);
        $data=['symbol'=>$sym,'close'=>(string)$p,'open'=>(string)$o,'high'=>(string)floatval($q['regularMarketDayHigh']??$p),'low'=>(string)floatval($q['regularMarketDayLow']??$p),'volume'=>(string)($q['regularMarketVolume']??0),'previous_close'=>(string)floatval($q['regularMarketPreviousClose']??$o),'market_cap'=>(string)($q['marketCap']??0),'pe_ratio'=>isset($q['trailingPE'])?(string)$q['trailingPE']:'','name'=>$q['shortName']??$info['name'],'change_percent'=>(string)floatval($q['regularMarketChangePercent']??0),'_source'=>'yahoo_v7'];
    }

    // Source 2: Yahoo v7 query2
    if(!$data||floatval($data['close']??0)<=0){
        $r2=hJson('https://query2.finance.yahoo.com/v7/finance/quote?symbols='.urlencode($ySym).'&fields=regularMarketPrice,regularMarketOpen,regularMarketDayHigh,regularMarketDayLow,regularMarketVolume,regularMarketPreviousClose,shortName',7);
        if($r2&&!empty($r2['quoteResponse']['result'][0]['regularMarketPrice'])){
            $q=$r2['quoteResponse']['result'][0];$p=floatval($q['regularMarketPrice']);$o=floatval($q['regularMarketOpen']??$p);
            $data=['symbol'=>$sym,'close'=>(string)$p,'open'=>(string)$o,'high'=>(string)floatval($q['regularMarketDayHigh']??$p),'low'=>(string)floatval($q['regularMarketDayLow']??$p),'volume'=>(string)($q['regularMarketVolume']??0),'previous_close'=>(string)floatval($q['regularMarketPreviousClose']??$o),'market_cap'=>(string)($q['marketCap']??0),'pe_ratio'=>'','name'=>$q['shortName']??$info['name'],'change_percent'=>'0','_source'=>'yahoo_v7q2'];
        }
    }

    // Source 3: Yahoo v8 chart
    if(!$data||floatval($data['close']??0)<=0){
        $r3=hJson('https://query1.finance.yahoo.com/v8/finance/chart/'.urlencode($ySym).'?interval=1d&range=1d',7);
        if($r3&&isset($r3['chart']['result'][0]['meta']['regularMarketPrice'])){
            $m=$r3['chart']['result'][0]['meta'];$p=floatval($m['regularMarketPrice']??0);$o=floatval($m['chartPreviousClose']??$p);
            if($p>0)$data=['symbol'=>$sym,'close'=>(string)$p,'open'=>(string)$o,'high'=>(string)floatval($m['regularMarketDayHigh']??$p),'low'=>(string)floatval($m['regularMarketDayLow']??$p),'volume'=>(string)($m['regularMarketVolume']??0),'previous_close'=>(string)$o,'market_cap'=>'0','pe_ratio'=>'','name'=>$m['shortName']??$info['name'],'change_percent'=>$o>0?(string)round(($p-$o)/$o*100,4):'0','_source'=>'yahoo_v8'];
        }
    }

    // Source 4: Finnhub
    if(!$data||floatval($data['close']??0)<=0){
        $fh=hJson('https://finnhub.io/api/v1/quote?symbol='.urlencode($sym).'&token='.FINNHUB_KEY,6);
        if($fh&&!empty($fh['c'])&&floatval($fh['c'])>0){
            $p=floatval($fh['c']);$o=floatval($fh['o']??$p);
            $data=['symbol'=>$sym,'close'=>(string)$p,'open'=>(string)$o,'high'=>(string)floatval($fh['h']??$p),'low'=>(string)floatval($fh['l']??$p),'volume'=>'0','previous_close'=>(string)floatval($fh['pc']??$o),'market_cap'=>'0','pe_ratio'=>'','name'=>$info['name'],'change_percent'=>$o>0?(string)round(($p-$o)/$o*100,4):'0','_source'=>'finnhub'];
        }
    }

    // Source 5: TwelveData
    if(!$data||floatval($data['close']??0)<=0){
        $td=hJson('https://api.twelvedata.com/quote?symbol='.urlencode($sym).'&apikey='.TWELVE_DATA_KEY,8);
        if($td&&!empty($td['close'])&&floatval($td['close'])>0&&empty($td['code'])){
            $data=$td;$data['_source']='twelvedata';$data['name']=$info['name'];
        }
    }

    // Stale cache fallback
    if(!$data||floatval($data['close']??0)<=0){
        try{$cs2=getDB()->prepare("SELECT data FROM price_cache WHERE symbol=?");$cs2->execute([$sym]);$cr2=$cs2->fetch();if($cr2){$sd=json_decode($cr2['data'],true);$sd['_stale']=true;$sd['_symbol_info']=$info;echo json_encode($sd);exit;}}catch(Exception $e){}
        // Tell JS to fetch directly - this is the key signal
        echo json_encode(['error'=>'server_blocked','_symbol_info'=>$info,'_direct_fetch'=>true]);exit;
    }

    $data['_symbol_info']=$info;
    try{$enc=json_encode($data);getDB()->prepare("INSERT INTO price_cache (symbol,data) VALUES (?,?) ON DUPLICATE KEY UPDATE data=?,updated_at=NOW()")->execute([$sym,$enc,$enc]);}catch(Exception $e){}
    echo json_encode($data);exit;
}

// ═══════════════════════════════════════════════════════════════
//  TIMESERIES
// ═══════════════════════════════════════════════════════════════
if ($action==='timeseries') {
    $sym=strtoupper(trim(clean($_GET['sym']??'')));
    $tf=clean($_GET['interval']??'5min');
    $size=max(20,min(300,intval($_GET['size']??80)));
    $info=symInfo($sym);
    if(!$info){echo json_encode(['error'=>"Unknown: $sym",'values'=>[]]);exit;}

    $tfMap=['1min'=>['yi'=>'1m','yr'=>'1d'],'5min'=>['yi'=>'5m','yr'=>'1d'],'15min'=>['yi'=>'15m','yr'=>'5d'],
            '30min'=>['yi'=>'30m','yr'=>'5d'],'1h'=>['yi'=>'60m','yr'=>'5d'],
            '1day'=>['yi'=>'1d','yr'=>'1y','si'=>'d','td'=>'1day'],
            '1week'=>['yi'=>'1wk','yr'=>'5y','si'=>'w','td'=>'1week'],
            '1month'=>['yi'=>'1mo','yr'=>'10y','si'=>'m','td'=>'1month']];
    $map=$tfMap[$tf]??$tfMap['5min'];
    $ySym=yS($sym); $values=null;

    // Yahoo Finance
    foreach(['https://query1.finance.yahoo.com','https://query2.finance.yahoo.com'] as $yHost){
        $yurl="$yHost/v8/finance/chart/".urlencode($ySym)."?interval={$map['yi']}&range={$map['yr']}";
        $yr=hJson($yurl,9);
        if($yr&&!empty($yr['chart']['result'][0]['timestamp'])){
            $res=$yr['chart']['result'][0];$ts=$res['timestamp'];
            $qd=$res['indicators']['quote'][0]??[];
            $cl=$qd['close']??[];$op=$qd['open']??[];$hi=$qd['high']??[];$lo=$qd['low']??[];$vo=$qd['volume']??[];
            $p=[];
            foreach($ts as $i=>$t){$c=$cl[$i]??null;if($c===null||$c==0)continue;
                $p[]=['datetime'=>date('Y-m-d H:i:00',$t),'open'=>number_format(floatval($op[$i]??$c),6,'.',''),'high'=>number_format(floatval($hi[$i]??$c),6,'.',''),'low'=>number_format(floatval($lo[$i]??$c),6,'.',''),'close'=>number_format(floatval($c),6,'.',''),'volume'=>(string)($vo[$i]??0)];}
            if(count($p)>=2){$values=array_slice(array_reverse($p),0,$size);break;}
        }
    }

    // Stooq fallback for daily/weekly/monthly
    if(!$values&&isset($map['si'])){
        $sm=['BTC/USD'=>'btc.v','ETH/USD'=>'eth.v','BNB/USD'=>'bnb.v','XRP/USD'=>'xrp.v','SOL/USD'=>'sol.v','DOGE/USD'=>'doge.v','EUR/USD'=>'eurusd','GBP/USD'=>'gbpusd','USD/JPY'=>'usdjpy','USD/INR'=>'usdinr','AUD/USD'=>'audusd','USD/CAD'=>'usdcad'];
        $ss=$sm[$sym]??strtolower($sym).'.us';
        $sr=hGet('https://stooq.com/q/d/l/?s='.urlencode($ss).'&i='.$map['si'],8);
        if($sr){$lines=array_values(array_filter(explode("\n",trim($sr))));$sv=[];
            for($i=count($lines)-1;$i>=1;$i--){$c=array_map('trim',explode(',',$lines[$i]));if(count($c)<5)continue;$cl=floatval($c[4]);if($cl<=0)continue;$sv[]=['datetime'=>$c[0].' 00:00:00','open'=>number_format(floatval($c[1]),6,'.',''),'high'=>number_format(floatval($c[2]),6,'.',''),'low'=>number_format(floatval($c[3]),6,'.',''),'close'=>number_format($cl,6,'.',''),'volume'=>trim($c[5]??'0')];}
            if(count($sv)>=2)$values=array_slice($sv,0,$size);}
    }

    // TwelveData fallback
    if(!$values&&isset($map['td'])){
        $now=time();if(!isset($_SESSION['td_c']))$_SESSION['td_c']=[];
        $_SESSION['td_c']=array_values(array_filter($_SESSION['td_c'],fn($t)=>($now-$t)<60));
        if(count($_SESSION['td_c'])<7){$_SESSION['td_c'][]=$now;
            $tdr=hJson('https://api.twelvedata.com/time_series?symbol='.urlencode($sym).'&interval='.$map['td'].'&outputsize='.$size.'&apikey='.TWELVE_DATA_KEY,9);
            if($tdr&&isset($tdr['values'])&&count($tdr['values'])>=2)$values=$tdr['values'];}
    }

    if(!$values||count($values)<2){
        // Signal JS to fetch directly from Yahoo
        echo json_encode(['error'=>'server_blocked','_direct_fetch'=>true,'_ysym'=>$ySym,'_yi'=>$map['yi'],'_yr'=>$map['yr'],'values'=>[]]);exit;
    }
    echo json_encode(['symbol'=>$sym,'values'=>$values]);exit;
}

// ═══════════════════════════════════════════════════════════════
//  INDICATORS
// ═══════════════════════════════════════════════════════════════
if ($action==='indicators') {
    $sym=strtoupper(trim(clean($_GET['sym']??'')));
    $info=symInfo($sym);if(!$info){echo json_encode(['error'=>'Invalid symbol']);exit;}
    $ySym=yS($sym);$closes=[];
    foreach(['query1','query2'] as $q){
        $raw=hJson("https://$q.finance.yahoo.com/v8/finance/chart/".urlencode($ySym)."?interval=1d&range=1y",9);
        if($raw&&isset($raw['chart']['result'][0]['indicators']['quote'][0]['close'])){
            $closes=array_values(array_filter($raw['chart']['result'][0]['indicators']['quote'][0]['close'],fn($v)=>is_numeric($v)&&$v>0));
            if(count($closes)>=14)break;
        }
    }
    if(count($closes)>=14){
        $n=count($closes);$g=$l=[];
        for($i=1;$i<$n;$i++){$d=$closes[$i]-$closes[$i-1];$g[]=max(0,$d);$l[]=max(0,-$d);}
        $ag=array_sum(array_slice($g,0,14))/14;$al=array_sum(array_slice($l,0,14))/14;
        for($i=14;$i<count($g);$i++){$ag=($ag*13+$g[$i])/14;$al=($al*13+$l[$i])/14;}
        $rsi=$al==0?100:round(100-(100/(1+$ag/max($al,0.00001))),2);
        $ema=function($p,$per){if(count($p)<$per)return null;$k=2/($per+1);$e=array_sum(array_slice($p,0,$per))/$per;for($i=$per;$i<count($p);$i++)$e=$p[$i]*$k+$e*(1-$k);return round($e,4);};
        $sma=function($p,$per){if(count($p)<$per)return null;return round(array_sum(array_slice($p,-$per))/$per,4);};
        $e12=$ema($closes,12);$e26=$ema($closes,26);$macd=($e12&&$e26)?round($e12-$e26,4):null;
        $s20=$sma($closes,20);$bbu=$bbl=null;
        if($s20&&$n>=20){$sl=array_slice($closes,-20);$std=sqrt(array_sum(array_map(fn($x)=>pow($x-$s20,2),$sl))/20);$bbu=round($s20+2*$std,4);$bbl=round($s20-2*$std,4);}
        echo json_encode(['rsi'=>['rsi'=>$rsi],'macd'=>['macd'=>$macd,'macd_signal'=>null],'ema20'=>['ema'=>$ema($closes,20)],'ema50'=>['ema'=>$ema($closes,50)],'sma200'=>['sma'=>$sma($closes,200)],'bbands'=>['upper_band'=>$bbu,'lower_band'=>$bbl,'middle_band'=>$s20],'adx'=>['adx'=>null],'stoch'=>['slow_k'=>null],'_source'=>'computed']);exit;
    }
    echo json_encode(['rsi'=>null,'macd'=>null,'ema20'=>null,'ema50'=>null,'sma200'=>null,'bbands'=>null,'adx'=>null,'stoch'=>null]);exit;
}

// ═══════════════════════════════════════════════════════════════
//  PROFILE / NEWS / FRED / AI PREDICT
// ═══════════════════════════════════════════════════════════════
if ($action==='profile') {
    $sym=strtoupper(trim(clean($_GET['sym']??'')));$info=symInfo($sym);
    if(!$info||strpos($sym,'/')!==false){echo json_encode(['error'=>'Not available']);exit;}
    $prof=hJson('https://finnhub.io/api/v1/stock/profile2?symbol='.urlencode($sym).'&token='.FINNHUB_KEY,7);
    $metr=hJson('https://finnhub.io/api/v1/stock/metric?symbol='.urlencode($sym).'&metric=all&token='.FINNHUB_KEY,7);
    $earn=hJson('https://finnhub.io/api/v1/stock/earnings?symbol='.urlencode($sym).'&limit=4&token='.FINNHUB_KEY,7);
    if(empty($prof['name'])){$yd=hJson('https://query2.finance.yahoo.com/v10/finance/quoteSummary/'.urlencode(yS($sym)).'?modules=assetProfile,summaryDetail,defaultKeyStatistics',8);$res=$yd['quoteSummary']['result'][0]??[];$ap=$res['assetProfile']??[];$sd=$res['summaryDetail']??[];$ks=$res['defaultKeyStatistics']??[];if(!empty($ap['longName']??'')){$prof=['name'=>$ap['longName']??$sym,'exchange'=>'','finnhubIndustry'=>$ap['industry']??'','weburl'=>$ap['website']??'','country'=>$ap['country']??''];$metr=['metric'=>['peNormalizedAnnual'=>$sd['trailingPE']['raw']??null,'marketCapitalization'=>$sd['marketCap']['raw']??null,'epsNormalizedAnnual'=>$ks['trailingEps']['raw']??null,'52WeekHigh'=>$sd['fiftyTwoWeekHigh']['raw']??null,'52WeekLow'=>$sd['fiftyTwoWeekLow']['raw']??null]];}}
    echo json_encode(['profile'=>$prof??[],'metrics'=>$metr['metric']??[],'earnings'=>$earn??[]]);exit;
}

if ($action==='news') {
    $sym=strtoupper(trim(clean($_GET['sym']??'')));$info=symInfo($sym);
    if(!$info){echo json_encode(['articles'=>[]]);exit;}
    $arts=[];$yn=hJson('https://query1.finance.yahoo.com/v1/finance/search?q='.urlencode(yS($sym)).'&newsCount=10&quotesCount=0',7);
    if(!empty($yn['news']))foreach($yn['news'] as $n)$arts[]=['source'=>['name'=>$n['publisher']??'Yahoo Finance'],'title'=>$n['title']??'','url'=>$n['link']??'#','publishedAt'=>isset($n['providerPublishTime'])?date('c',$n['providerPublishTime']):'','description'=>$n['summary']??''];
    if(count($arts)<5&&defined('NEWS_API_KEY')&&NEWS_API_KEY){$na=hJson('https://newsapi.org/v2/everything?q='.urlencode($info['name']??$sym).'&sortBy=publishedAt&pageSize=8&apiKey='.NEWS_API_KEY,7);if(!empty($na['articles']))foreach($na['articles'] as $a)$arts[]=$a;}
    echo json_encode(['articles'=>array_slice($arts,0,10)]);exit;
}

if ($action==='fred') {
    $series=['FEDFUNDS','CPIAUCSL','UNRATE','GDP','DGS10','DGS2','DGS30','VIXCLS','DEXUSEU','DEXJPUS','DCOILWTICO','GOLDAMGBD228NLBM'];
    $out=[];foreach($series as $id){$r=hJson('https://api.stlouisfed.org/fred/series/observations?series_id='.$id.'&api_key='.FRED_API_KEY.'&file_type=json&limit=1&sort_order=desc',7);$out[$id]=$r['observations'][0]??null;}
    echo json_encode($out);exit;
}

if ($action==='predict') {
    if(!isLoggedIn()){echo json_encode(['error'=>'Sign in required']);exit;}
    $sym=strtoupper(trim(clean($_POST['sym']??'')));$info=symInfo($sym);
    if(!$info){echo json_encode(['error'=>'Unknown symbol']);exit;}
    $md=json_decode($_POST['market_data']??'{}',true);if(empty($md)){echo json_encode(['error'=>'No market data']);exit;}
    $pr="You are a senior quantitative analyst. Analyze LIVE data for $sym ({$info['name']}).\n\nDATA:\n".json_encode($md,JSON_PRETTY_PRINT)."\n\nProvide: 1)SIGNAL BUY/SELL/HOLD 2)7-day target 3)Technicals 4)Support/Resistance 5)Risk LOW/MEDIUM/HIGH 6)Entry/stop/target\n\nEnd: CONFIDENCE: XX% | SIGNAL: BUY/SELL/HOLD | RISK: LOW/MEDIUM/HIGH";
    $pl=json_encode(['model'=>'llama-3.3-70b-versatile','messages'=>[['role'=>'user','content'=>$pr]],'temperature'=>0.25,'max_tokens'=>1200]);
    $resp=null;
    if(function_exists('curl_init')){$ch=curl_init('https://api.groq.com/openai/v1/chat/completions');curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$pl,CURLOPT_TIMEOUT=>30,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.GROQ_API_KEY],CURLOPT_SSL_VERIFYPEER=>false]);$resp=curl_exec($ch);curl_close($ch);}
    else{$ctx=stream_context_create(['http'=>['method'=>'POST','timeout'=>30,'header'=>"Content-Type: application/json\r\nAuthorization: Bearer ".GROQ_API_KEY."\r\n",'content'=>$pl]]);$resp=@file_get_contents('https://api.groq.com/openai/v1/chat/completions',false,$ctx);}
    if(!$resp){echo json_encode(['error'=>'AI unavailable']);exit;}
    $d=json_decode($resp,true);$text=$d['choices'][0]['message']['content']??'';
    if(empty($text)){echo json_encode(['error'=>'No AI response']);exit;}
    preg_match('/CONFIDENCE:\s*(\d+)%\s*\|\s*SIGNAL:\s*(BUY|SELL|HOLD)\s*\|\s*RISK:\s*(LOW|MEDIUM|HIGH)/i',$text,$m);
    $conf=intval($m[1]??70);$signal=strtoupper($m[2]??'HOLD');$risk=strtoupper($m[3]??'MEDIUM');
    $p=floatval($md['currentPrice']??0);$tgt=$p*($signal==='BUY'?1.04:($signal==='SELL'?0.96:1.01));
    if(preg_match('/(?:target)[^$\d]*\$?([\d,]+\.?\d*)/i',$text,$tm)){$t=floatval(str_replace(',','',$tm[1]));if($p>0&&$t>$p*0.5&&$t<$p*2)$tgt=$t;}
    try{getDB()->prepare("INSERT INTO predictions (user_id,symbol,signal,target_price,confidence,risk,summary,raw_data) VALUES (?,?,?,?,?,?,?,?)")->execute([$_SESSION['user_id'],$sym,$signal,$tgt,$conf,$risk,substr($text,0,2000),json_encode($md)]);}catch(Exception $e){}
    echo json_encode(['text'=>$text,'signal'=>$signal,'risk'=>$risk,'confidence'=>$conf,'target'=>$tgt,'symbol'=>$sym]);exit;
}

if ($action==='watchlist_add'){if(!isLoggedIn()){echo json_encode(['error'=>'Login required']);exit;}$sym=strtoupper(trim(clean($_POST['sym']??'')));$info=symInfo($sym);if(!$info){echo json_encode(['error'=>'Invalid symbol']);exit;}try{getDB()->prepare("INSERT IGNORE INTO watchlist (user_id,symbol,name,asset_type) VALUES (?,?,?,?)")->execute([$_SESSION['user_id'],$sym,$info['name']??$sym,$info['asset_type']??'stock']);echo json_encode(['ok'=>true,'sym'=>$sym]);}catch(Exception $e){echo json_encode(['error'=>'DB error']);}exit;}
if ($action==='watchlist_remove'){if(!isLoggedIn()){echo json_encode(['error'=>'Login required']);exit;}$sym=strtoupper(trim(clean($_POST['sym']??'')));try{getDB()->prepare("DELETE FROM watchlist WHERE user_id=? AND symbol=?")->execute([$_SESSION['user_id'],$sym]);}catch(Exception $e){}echo json_encode(['ok'=>true]);exit;}
if ($action==='watchlist_get'){if(!isLoggedIn()){echo json_encode(['items'=>[]]);exit;}try{$s=getDB()->prepare("SELECT symbol,name,asset_type FROM watchlist WHERE user_id=? ORDER BY added_at DESC");$s->execute([$_SESSION['user_id']]);echo json_encode(['items'=>$s->fetchAll()]);}catch(Exception $e){echo json_encode(['items'=>[]]);}exit;}
if ($action==='my_predictions'){if(!isLoggedIn()){echo json_encode(['items'=>[]]);exit;}try{$s=getDB()->prepare("SELECT symbol,signal,target_price,confidence,risk,created_at FROM predictions WHERE user_id=? ORDER BY created_at DESC LIMIT 20");$s->execute([$_SESSION['user_id']]);echo json_encode(['items'=>$s->fetchAll()]);}catch(Exception $e){echo json_encode(['items'=>[]]);}exit;}

echo json_encode(['error'=>'Unknown action: '.htmlspecialchars($action)]);
