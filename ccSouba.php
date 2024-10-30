<?php

/*
  Plugin Name: ccSouba
  Plugin URI: http://accountingse.net/2012/09/582/
  Description: "ccSouba" is a plugin providing cryptocurrency price. How to use: http://accountingse.net/2016/07/971/ (Sorry, Japanese only...)
  Version: 0.0.3
  Author: kazunii_ac
  Author URI: https://twitter.com/kazunii_ac
  License: GPL2
 */

/*  Copyright 2016 kazunii_ac (email : moskov@mcn.ne.jp)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

function ccSoubaFunc($atts) {
    $LastCheckTimeArr = explode('|', get_option('ccSouba_lastcheck'));
    $LastCheckUnixTime = (integer) $LastCheckTimeArr[0];

    if ($LastCheckUnixTime + (60 * 60 * 1) < time()) {
        //前回取得から1時間経ったのでjsonを取得する
        //zaif ticker
        update_option('ccSouba_jsons_zaif_ticker_btc_jpy.json', file_get_contents('https://api.zaif.jp/api/1/ticker/btc_jpy'));
        update_option('ccSouba_jsons_zaif_ticker_xem_jpy.json', file_get_contents('https://api.zaif.jp/api/1/ticker/xem_jpy'));
        update_option('ccSouba_jsons_zaif_ticker_mona_jpy.json', file_get_contents('https://api.zaif.jp/api/1/ticker/mona_jpy'));
        update_option('ccSouba_jsons_zaif_ticker_mona_btc.json', file_get_contents('https://api.zaif.jp/api/1/ticker/mona_btc'));
        update_option('ccSouba_jsons_zaif_ticker_xem_btc.json', file_get_contents('https://api.zaif.jp/api/1/ticker/xem_btc'));

        //poloniex ticker
        update_option('ccSouba_jsons_poloniex_ticker.json', file_get_contents('https://poloniex.com/public?command=returnTicker'));

        //チェック日付時刻更新
        update_option('ccSouba_lastcheck', time() . "|" . date('Y-m-d h:i:s'));
    }
    $RetStr = '';
    if (empty($atts['class'])) {
        $RetStr .= '<span class="ccSouba">';
    } else {
        $RetStr .= '<span class="' . $atts['class'] . '">';
    }

    if ($atts['exchange'] === 'zaif') {
        $JsonZaif = get_option('ccSouba_jsons_zaif_ticker_' . $atts['currencypair'] . '.json');
        if (!empty($JsonZaif)) {
            //ファイルがある。正常。
            $Cls = json_decode($JsonZaif);
            if (empty($atts['head'])) {
                $TempArr = explode('_', $atts['currencypair']);
                $RetStr .= '1' . strtoupper($TempArr[0]) . ' is ';
            } else {
                $RetStr .= $atts['head'];
            }
            $RetStr .= ccSoubaNumIikanji($Cls->last);
            if (empty($atts['tail'])) {
                $TempArr = explode('_', $atts['currencypair']);
                $RetStr .= strtoupper($TempArr[1]) . '.';
            } else {
                $RetStr .= $atts['tail'];
            }
        } else {
            $RetStr .= 'ccSouba: ERROR! currency pair choice wrong. error code: 78546';
        }
    } elseif ($atts['exchange'] === 'poloniex' || empty($atts['exchange'])) {
        $JsonPoloniex = get_option('ccSouba_jsons_poloniex_ticker.json');
        $Cls = json_decode($JsonPoloniex);
        if (!empty($JsonPoloniex)) {
            //ファイルがある。正常。
            if ($atts['help'] === 'true') {
                //poloniexの通貨一覧を表示する。
                $RetStr .= '<ul>';
                foreach ($Cls as $Key => $Value) {
                    $RetStr .= '<li>' . strtolower($Key) . '</li>';
                }
                $RetStr .= '</ul>';
            } else {
                //普通に相場を表示。
                $atts['currencypair'] = strtoupper($atts['currencypair']);
                $Flg = 0;
                foreach ($Cls as $Key => $Value) {
                    if ($Key === $atts['currencypair']) {
                        if (empty($atts['head'])) {
                            $TempArr = explode('_', $atts['currencypair']);
                            $RetStr .= '1' . strtoupper($TempArr[1]) . ' is ';
                        } else {
                            $RetStr .= $atts['head'];
                        }
                        $RetStr .= ccSoubaNumIikanji($Value->last);
                        if (empty($atts['tail'])) {
                            $TempArr = explode('_', $atts['currencypair']);
                            $RetStr .= strtoupper($TempArr[0]) . '.';
                        } else {
                            $RetStr .= $atts['tail'];
                        }
                        $Flg = 1;
                    }
                }
                if ($Flg === 0) {
                    $RetStr .= 'ccSouba: ERROR! currency pair choice wrong. error code: 15754';
                }
            }
        } else {
            $RetStr .= 'ccSouba: ERROR! something wrong. please call <a href="https://twitter.com/kazunii_ac">@kazunii_ac</a>.  error code: 45766';
        }
    } else {
        $RetStr = 'ccSouba: ERROR! exchange choice wrong. error code: 78576';
    }
    $RetStr .= '</span>';
    return $RetStr;
}

function ccSoubaKouchikuNumHTML($atts, $JsonFilePath) {
    $RetStr = '';
    return($RetStr);
}

function ccSoubaNumIikanji($Num) {
    $NumStr = number_format($Num, 20); // 1,200.50
    $NumStr = preg_replace("/\.?0+$/", "", $NumStr); // 1,200.5
    return($NumStr);
}

add_shortcode('ccSouba', 'ccSoubaFunc');
?>