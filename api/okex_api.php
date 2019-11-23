<?php

error_reporting(0);
date_default_timezone_set(DateTimeZone::listIdentifiers(DateTimeZone::UTC)[0]);

if(isset($_POST['method'])) { $method = $_POST['method']; } elseif(isset($_GET['method'])) { $method = $_GET['method']; } else { $method = ''; }
$method = htmlspecialchars(strip_tags(trim($method)));
if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
$key = htmlspecialchars(strip_tags(trim($key)));

//---------------------------- get Rules

if ($method == 'getRules') {

	$rules = "{";
	$rules .= "\"success\":1,";
	$rules .= "\"serverTime\":".round($fcontents['serverTime']/1000,0).",";

	$coins = [
'1st',
'aac',
'abt',
'ace',
'act',
'aidoc',
'amm',
'ark',
'ast',
'atl',
'auto',
'avt',
'bcd',
'bch',
'bcx',
'bec',
'bkx',
'bnt',
'brd',
'bt2',
'btc',
'btg',
'btm',
'cag',
'can',
'cbt',
'chat',
'cic',
'cmt',
'ctr',
'cvc',
'dadi',
'dash',
'dat',
'dent',
'dgb',
'dgd',
'dna',
'dnt',
'dpy',
'edo',
'elf',
'eng',
'enj',
'eos',
'etc',
'eth',
'evx',
'fair',
'fun',
'gas',
'gnt',
'gnx',
'gsc',
'gtc',
'gto',
'hmc',
'hot',
'hsr',
'icn',
'icx',
'ins',
'insur',
'int',
'iost',
'iota',
'ipc',
'itc',
'kcash',
'key',
'knc',
'la',
'lend',
'lev',
'light',
'link',
'lrc',
'ltc',
'mag',
'mana',
'mco',
'mda',
'mdt',
'mith',
'mkr',
'mof',
'mot',
'mth',
'mtl',
'nano',
'nas',
'neo',
'ngc',
'nuls',
'oax',
'of',
'omg',
'ost',
'pay',
'poe',
'ppt',
'pra',
'pst',
'qtum',
'qun',
'qvt',
'r',
'rcn',
'rct',
'rdn',
'read',
'ref',
'ren',
'req',
'rfr',
'rnt',
'salt',
'san',
'sbtc',
'show',
'smt',
'snc',
'sngls',
'snm',
'snt',
'soc',
'spf',
'ssc',
'stc',
'storj',
'sub',
'swftc',
'tct',
'theta',
'tio',
'tnb',
'topc',
'tra',
'trio',
'true',
'trx',
'ubtc',
'uct',
'ugc',
'ukg',
'utk',
'vee',
'vib',
'viu',
'wbtc',
'wfee',
'wrc',
'wtc',
'xem',
'xlm',
'xmr',
'xrp',
'xuc',
'yee',
'yoyo',
'zec',
'zen',
'zip',
'zrx'
	];

	$rules .= "\"coins\":[";

	foreach ($coins as $value) {
		$rules .= "\"$value\",";
	}
	$rules = substr($rules, 0, -1) . "],";
	$rules .= "\"pairs\":{";
	$pairs = [
'1st_btc',
'1st_eth',
'1st_usdt',
'aac_btc',
'aac_eth',
'aac_usdt',
'abt_btc',
'abt_eth',
'abt_usdt',
'ace_btc',
'ace_eth',
'ace_usdt',
'act_bch',
'act_btc',
'act_eth',
'act_usdt',
'aidoc_btc',
'aidoc_eth',
'aidoc_usdt',
'amm_btc',
'amm_eth',
'amm_usdt',
'ark_btc',
'ark_eth',
'ark_usdt',
'ast_btc',
'ast_eth',
'ast_usdt',
'atl_btc',
'atl_eth',
'atl_usdt',
'auto_btc',
'auto_eth',
'auto_usdt',
'avt_bch',
'avt_btc',
'avt_eth',
'avt_usdt',
'bcd_bch',
'bcd_btc',
'bcd_usdt',
'bch_btc',
'bch_eth',
'bch_usdt',
'bcx_bch',
'bcx_btc',
'bec_btc',
'bec_eth',
'bec_usdt',
'bkx_btc',
'bkx_eth',
'bkx_usdt',
'bnt_btc',
'bnt_eth',
'bnt_usdt',
'brd_btc',
'brd_eth',
'brd_usdt',
'bt2_btc',
'btc_usdt',
'btg_bch',
'btg_btc',
'btg_usdt',
'btm_btc',
'btm_eth',
'btm_usdt',
'cag_btc',
'cag_eth',
'cag_usdt',
'can_btc',
'can_eth',
'can_usdt',
'cbt_btc',
'cbt_eth',
'cbt_usdt',
'chat_btc',
'chat_eth',
'chat_usdt',
'cic_btc',
'cic_eth',
'cic_usdt',
'cmt_bch',
'cmt_btc',
'cmt_eth',
'cmt_usdt',
'ctr_btc',
'ctr_eth',
'ctr_usdt',
'cvc_btc',
'cvc_eth',
'cvc_usdt',
'dadi_btc',
'dadi_eth',
'dadi_usdt',
'dash_bch',
'dash_btc',
'dash_eth',
'dash_usdt',
'dat_btc',
'dat_eth',
'dat_usdt',
'dent_btc',
'dent_eth',
'dent_usdt',
'dgb_btc',
'dgb_eth',
'dgb_usdt',
'dgd_bch',
'dgd_btc',
'dgd_eth',
'dgd_usdt',
'dna_btc',
'dna_eth',
'dna_usdt',
'dnt_btc',
'dnt_eth',
'dnt_usdt',
'dpy_btc',
'dpy_eth',
'dpy_usdt',
'edo_bch',
'edo_btc',
'edo_eth',
'edo_usdt',
'elf_btc',
'elf_eth',
'elf_usdt',
'eng_btc',
'eng_eth',
'eng_usdt',
'enj_btc',
'enj_eth',
'enj_usdt',
'eos_bch',
'eos_btc',
'eos_eth',
'eos_usdt',
'etc_bch',
'etc_btc',
'etc_eth',
'etc_usdt',
'eth_btc',
'eth_usdt',
'evx_btc',
'evx_eth',
'evx_usdt',
'fair_btc',
'fair_eth',
'fair_usdt',
'fun_btc',
'fun_eth',
'fun_usdt',
'gas_btc',
'gas_eth',
'gas_usdt',
'gnt_btc',
'gnt_eth',
'gnt_usdt',
'gnx_btc',
'gnx_eth',
'gnx_usdt',
'gsc_btc',
'gsc_eth',
'gsc_usdt',
'gtc_btc',
'gtc_eth',
'gtc_usdt',
'gto_btc',
'gto_eth',
'gto_usdt',
'hmc_btc',
'hmc_eth',
'hmc_usdt',
'hot_btc',
'hot_eth',
'hot_usdt',
'hsr_btc',
'hsr_eth',
'hsr_usdt',
'icn_btc',
'icn_eth',
'icn_usdt',
'icx_btc',
'icx_eth',
'icx_usdt',
'ins_btc',
'ins_eth',
'ins_usdt',
'insur_btc',
'insur_eth',
'insur_usdt',
'int_btc',
'int_eth',
'int_usdt',
'iost_btc',
'iost_eth',
'iost_usdt',
'iota_btc',
'iota_eth',
'iota_usdt',
'ipc_btc',
'ipc_eth',
'ipc_usdt',
'itc_btc',
'itc_eth',
'itc_usdt',
'kcash_btc',
'kcash_eth',
'kcash_usdt',
'key_btc',
'key_eth',
'key_usdt',
'knc_btc',
'knc_eth',
'knc_usdt',
'la_btc',
'la_eth',
'la_usdt',
'lend_btc',
'lend_eth',
'lend_usdt',
'lev_btc',
'lev_eth',
'lev_usdt',
'light_btc',
'light_eth',
'light_usdt',
'link_btc',
'link_eth',
'link_usdt',
'lrc_btc',
'lrc_eth',
'lrc_usdt',
'ltc_bch',
'ltc_btc',
'ltc_eth',
'ltc_usdt',
'mag_btc',
'mag_eth',
'mag_usdt',
'mana_btc',
'mana_eth',
'mana_usdt',
'mco_btc',
'mco_eth',
'mco_usdt',
'mda_btc',
'mda_eth',
'mda_usdt',
'mdt_btc',
'mdt_eth',
'mdt_usdt',
'mith_btc',
'mith_eth',
'mith_usdt',
'mkr_btc',
'mkr_eth',
'mkr_usdt',
'mof_btc',
'mof_eth',
'mof_usdt',
'mot_btc',
'mot_eth',
'mot_usdt',
'mth_btc',
'mth_eth',
'mth_usdt',
'mtl_btc',
'mtl_eth',
'mtl_usdt',
'nano_btc',
'nano_eth',
'nano_usdt',
'nas_btc',
'nas_eth',
'nas_usdt',
'neo_btc',
'neo_eth',
'neo_usdt',
'ngc_btc',
'ngc_eth',
'ngc_usdt',
'nuls_btc',
'nuls_eth',
'nuls_usdt',
'oax_btc',
'oax_eth',
'oax_usdt',
'of_btc',
'of_eth',
'of_usdt',
'omg_btc',
'omg_eth',
'omg_usdt',
'ost_btc',
'ost_eth',
'ost_usdt',
'pay_btc',
'pay_eth',
'pay_usdt',
'poe_btc',
'poe_eth',
'poe_usdt',
'ppt_btc',
'ppt_eth',
'ppt_usdt',
'pra_btc',
'pra_eth',
'pra_usdt',
'pst_btc',
'pst_eth',
'pst_usdt',
'qtum_btc',
'qtum_eth',
'qtum_usdt',
'qun_btc',
'qun_eth',
'qun_usdt',
'qvt_btc',
'qvt_eth',
'qvt_usdt',
'r_btc',
'r_eth',
'r_usdt',
'rcn_btc',
'rcn_eth',
'rcn_usdt',
'rct_btc',
'rct_eth',
'rct_usdt',
'rdn_btc',
'rdn_eth',
'rdn_usdt',
'read_btc',
'read_eth',
'read_usdt',
'ref_btc',
'ref_eth',
'ref_usdt',
'ren_btc',
'ren_eth',
'ren_usdt',
'req_btc',
'req_eth',
'req_usdt',
'rfr_btc',
'rfr_eth',
'rfr_usdt',
'rnt_btc',
'rnt_eth',
'rnt_usdt',
'salt_btc',
'salt_eth',
'salt_usdt',
'san_btc',
'san_eth',
'san_usdt',
'sbtc_bch',
'sbtc_btc',
'show_btc',
'show_eth',
'show_usdt',
'smt_btc',
'smt_eth',
'smt_usdt',
'snc_btc',
'snc_eth',
'snc_usdt',
'sngls_btc',
'sngls_eth',
'sngls_usdt',
'snm_btc',
'snm_eth',
'snm_usdt',
'snt_btc',
'snt_eth',
'snt_usdt',
'soc_btc',
'soc_eth',
'soc_usdt',
'spf_btc',
'spf_eth',
'spf_usdt',
'ssc_btc',
'ssc_eth',
'ssc_usdt',
'stc_btc',
'stc_eth',
'stc_usdt',
'storj_btc',
'storj_eth',
'storj_usdt',
'sub_btc',
'sub_eth',
'sub_usdt',
'swftc_btc',
'swftc_eth',
'swftc_usdt',
'tct_btc',
'tct_eth',
'tct_usdt',
'theta_btc',
'theta_eth',
'theta_usdt',
'tio_btc',
'tio_eth',
'tio_usdt',
'tnb_btc',
'tnb_eth',
'tnb_usdt',
'topc_btc',
'topc_eth',
'topc_usdt',
'tra_btc',
'tra_eth',
'tra_usdt',
'trio_btc',
'trio_eth',
'trio_usdt',
'true_btc',
'true_eth',
'true_usdt',
'trx_btc',
'trx_eth',
'trx_usdt',
'ubtc_btc',
'ubtc_eth',
'ubtc_usdt',
'uct_btc',
'uct_eth',
'uct_usdt',
'ugc_btc',
'ugc_eth',
'ugc_usdt',
'ukg_btc',
'ukg_eth',
'ukg_usdt',
'utk_btc',
'utk_eth',
'utk_usdt',
'vee_btc',
'vee_eth',
'vee_usdt',
'vib_btc',
'vib_eth',
'vib_usdt',
'viu_btc',
'viu_eth',
'viu_usdt',
'wbtc_btc',
'wfee_btc',
'wfee_eth',
'wfee_usdt',
'wrc_btc',
'wrc_eth',
'wrc_usdt',
'wtc_btc',
'wtc_eth',
'wtc_usdt',
'xem_btc',
'xem_eth',
'xem_usdt',
'xlm_btc',
'xlm_eth',
'xlm_usdt',
'xmr_btc',
'xmr_eth',
'xmr_usdt',
'xrp_btc',
'xrp_eth',
'xrp_usdt',
'xuc_btc',
'xuc_eth',
'xuc_usdt',
'yee_btc',
'yee_eth',
'yee_usdt',
'yoyo_btc',
'yoyo_eth',
'yoyo_usdt',
'zec_btc',
'zec_eth',
'zec_usdt',
'zen_btc',
'zen_eth',
'zen_usdt',
'zip_btc',
'zip_eth',
'zip_usdt',
'zrx_btc',
'zrx_eth',
'zrx_usdt'
	];
	$symbols = [
'1STBTC',
'1STETH',
'1STUSDT',
'AACBTC',
'AACETH',
'AACUSDT',
'ABTBTC',
'ABTETH',
'ABTUSDT',
'ACEBTC',
'ACEETH',
'ACEUSDT',
'ACTBCH',
'ACTBTC',
'ACTETH',
'ACTUSDT',
'AIDOCBTC',
'AIDOCETH',
'AIDOCUSDT',
'AMMBTC',
'AMMETH',
'AMMUSDT',
'ARKBTC',
'ARKETH',
'ARKUSDT',
'ASTBTC',
'ASTETH',
'ASTUSDT',
'ATLBTC',
'ATLETH',
'ATLUSDT',
'AUTOBTC',
'AUTOETH',
'AUTOUSDT',
'AVTBCH',
'AVTBTC',
'AVTETH',
'AVTUSDT',
'BCDBCH',
'BCDBTC',
'BCDUSDT',
'BCHBTC',
'BCHETH',
'BCHUSDT',
'BCXBCH',
'BCXBTC',
'BECBTC',
'BECETH',
'BECUSDT',
'BKXBTC',
'BKXETH',
'BKXUSDT',
'BNTBTC',
'BNTETH',
'BNTUSDT',
'BRDBTC',
'BRDETH',
'BRDUSDT',
'BT2BTC',
'BTCUSDT',
'BTGBCH',
'BTGBTC',
'BTGUSDT',
'BTMBTC',
'BTMETH',
'BTMUSDT',
'CAGBTC',
'CAGETH',
'CAGUSDT',
'CANBTC',
'CANETH',
'CANUSDT',
'CBTBTC',
'CBTETH',
'CBTUSDT',
'CHATBTC',
'CHATETH',
'CHATUSDT',
'CICBTC',
'CICETH',
'CICUSDT',
'CMTBCH',
'CMTBTC',
'CMTETH',
'CMTUSDT',
'CTRBTC',
'CTRETH',
'CTRUSDT',
'CVCBTC',
'CVCETH',
'CVCUSDT',
'DADIBTC',
'DADIETH',
'DADIUSDT',
'DASHBCH',
'DASHBTC',
'DASHETH',
'DASHUSDT',
'DATBTC',
'DATETH',
'DATUSDT',
'DENTBTC',
'DENTETH',
'DENTUSDT',
'DGBBTC',
'DGBETH',
'DGBUSDT',
'DGDBCH',
'DGDBTC',
'DGDETH',
'DGDUSDT',
'DNABTC',
'DNAETH',
'DNAUSDT',
'DNTBTC',
'DNTETH',
'DNTUSDT',
'DPYBTC',
'DPYETH',
'DPYUSDT',
'EDOBCH',
'EDOBTC',
'EDOETH',
'EDOUSDT',
'ELFBTC',
'ELFETH',
'ELFUSDT',
'ENGBTC',
'ENGETH',
'ENGUSDT',
'ENJBTC',
'ENJETH',
'ENJUSDT',
'EOSBCH',
'EOSBTC',
'EOSETH',
'EOSUSDT',
'ETCBCH',
'ETCBTC',
'ETCETH',
'ETCUSDT',
'ETHBTC',
'ETHUSDT',
'EVXBTC',
'EVXETH',
'EVXUSDT',
'FAIRBTC',
'FAIRETH',
'FAIRUSDT',
'FUNBTC',
'FUNETH',
'FUNUSDT',
'GASBTC',
'GASETH',
'GASUSDT',
'GNTBTC',
'GNTETH',
'GNTUSDT',
'GNXBTC',
'GNXETH',
'GNXUSDT',
'GSCBTC',
'GSCETH',
'GSCUSDT',
'GTCBTC',
'GTCETH',
'GTCUSDT',
'GTOBTC',
'GTOETH',
'GTOUSDT',
'HMCBTC',
'HMCETH',
'HMCUSDT',
'HOTBTC',
'HOTETH',
'HOTUSDT',
'HSRBTC',
'HSRETH',
'HSRUSDT',
'ICNBTC',
'ICNETH',
'ICNUSDT',
'ICXBTC',
'ICXETH',
'ICXUSDT',
'INSBTC',
'INSETH',
'INSUSDT',
'INSURBTC',
'INSURETH',
'INSURUSDT',
'INTBTC',
'INTETH',
'INTUSDT',
'IOSTBTC',
'IOSTETH',
'IOSTUSDT',
'IOTABTC',
'IOTAETH',
'IOTAUSDT',
'IPCBTC',
'IPCETH',
'IPCUSDT',
'ITCBTC',
'ITCETH',
'ITCUSDT',
'KCASHBTC',
'KCASHETH',
'KCASHUSDT',
'KEYBTC',
'KEYETH',
'KEYUSDT',
'KNCBTC',
'KNCETH',
'KNCUSDT',
'LABTC',
'LAETH',
'LAUSDT',
'LENDBTC',
'LENDETH',
'LENDUSDT',
'LEVBTC',
'LEVETH',
'LEVUSDT',
'LIGHTBTC',
'LIGHTETH',
'LIGHTUSDT',
'LINKBTC',
'LINKETH',
'LINKUSDT',
'LRCBTC',
'LRCETH',
'LRCUSDT',
'LTCBCH',
'LTCBTC',
'LTCETH',
'LTCUSDT',
'MAGBTC',
'MAGETH',
'MAGUSDT',
'MANABTC',
'MANAETH',
'MANAUSDT',
'MCOBTC',
'MCOETH',
'MCOUSDT',
'MDABTC',
'MDAETH',
'MDAUSDT',
'MDTBTC',
'MDTETH',
'MDTUSDT',
'MITHBTC',
'MITHETH',
'MITHUSDT',
'MKRBTC',
'MKRETH',
'MKRUSDT',
'MOFBTC',
'MOFETH',
'MOFUSDT',
'MOTBTC',
'MOTETH',
'MOTUSDT',
'MTHBTC',
'MTHETH',
'MTHUSDT',
'MTLBTC',
'MTLETH',
'MTLUSDT',
'NANOBTC',
'NANOETH',
'NANOUSDT',
'NASBTC',
'NASETH',
'NASUSDT',
'NEOBTC',
'NEOETH',
'NEOUSDT',
'NGCBTC',
'NGCETH',
'NGCUSDT',
'NULSBTC',
'NULSETH',
'NULSUSDT',
'OAXBTC',
'OAXETH',
'OAXUSDT',
'OFBTC',
'OFETH',
'OFUSDT',
'OMGBTC',
'OMGETH',
'OMGUSDT',
'OSTBTC',
'OSTETH',
'OSTUSDT',
'PAYBTC',
'PAYETH',
'PAYUSDT',
'POEBTC',
'POEETH',
'POEUSDT',
'PPTBTC',
'PPTETH',
'PPTUSDT',
'PRABTC',
'PRAETH',
'PRAUSDT',
'PSTBTC',
'PSTETH',
'PSTUSDT',
'QTUMBTC',
'QTUMETH',
'QTUMUSDT',
'QUNBTC',
'QUNETH',
'QUNUSDT',
'QVTBTC',
'QVTETH',
'QVTUSDT',
'RBTC',
'RETH',
'RUSDT',
'RCNBTC',
'RCNETH',
'RCNUSDT',
'RCTBTC',
'RCTETH',
'RCTUSDT',
'RDNBTC',
'RDNETH',
'RDNUSDT',
'READBTC',
'READETH',
'READUSDT',
'REFBTC',
'REFETH',
'REFUSDT',
'RENBTC',
'RENETH',
'RENUSDT',
'REQBTC',
'REQETH',
'REQUSDT',
'RFRBTC',
'RFRETH',
'RFRUSDT',
'RNTBTC',
'RNTETH',
'RNTUSDT',
'SALTBTC',
'SALTETH',
'SALTUSDT',
'SANBTC',
'SANETH',
'SANUSDT',
'SBTCBCH',
'SBTCBTC',
'SHOWBTC',
'SHOWETH',
'SHOWUSDT',
'SMTBTC',
'SMTETH',
'SMTUSDT',
'SNCBTC',
'SNCETH',
'SNCUSDT',
'SNGLSBTC',
'SNGLSETH',
'SNGLSUSDT',
'SNMBTC',
'SNMETH',
'SNMUSDT',
'SNTBTC',
'SNTETH',
'SNTUSDT',
'SOCBTC',
'SOCETH',
'SOCUSDT',
'SPFBTC',
'SPFETH',
'SPFUSDT',
'SSCBTC',
'SSCETH',
'SSCUSDT',
'STCBTC',
'STCETH',
'STCUSDT',
'STORJBTC',
'STORJETH',
'STORJUSDT',
'SUBBTC',
'SUBETH',
'SUBUSDT',
'SWFTCBTC',
'SWFTCETH',
'SWFTCUSDT',
'TCTBTC',
'TCTETH',
'TCTUSDT',
'THETABTC',
'THETAETH',
'THETAUSDT',
'TIOBTC',
'TIOETH',
'TIOUSDT',
'TNBBTC',
'TNBETH',
'TNBUSDT',
'TOPCBTC',
'TOPCETH',
'TOPCUSDT',
'TRABTC',
'TRAETH',
'TRAUSDT',
'TRIOBTC',
'TRIOETH',
'TRIOUSDT',
'TRUEBTC',
'TRUEETH',
'TRUEUSDT',
'TRXBTC',
'TRXETH',
'TRXUSDT',
'UBTCBTC',
'UBTCETH',
'UBTCUSDT',
'UCTBTC',
'UCTETH',
'UCTUSDT',
'UGCBTC',
'UGCETH',
'UGCUSDT',
'UKGBTC',
'UKGETH',
'UKGUSDT',
'UTKBTC',
'UTKETH',
'UTKUSDT',
'VEEBTC',
'VEEETH',
'VEEUSDT',
'VIBBTC',
'VIBETH',
'VIBUSDT',
'VIUBTC',
'VIUETH',
'VIUUSDT',
'WBTCBTC',
'WFEEBTC',
'WFEEETH',
'WFEEUSDT',
'WRCBTC',
'WRCETH',
'WRCUSDT',
'WTCBTC',
'WTCETH',
'WTCUSDT',
'XEMBTC',
'XEMETH',
'XEMUSDT',
'XLMBTC',
'XLMETH',
'XLMUSDT',
'XMRBTC',
'XMRETH',
'XMRUSDT',
'XRPBTC',
'XRPETH',
'XRPUSDT',
'XUCBTC',
'XUCETH',
'XUCUSDT',
'YEEBTC',
'YEEETH',
'YEEUSDT',
'YOYOBTC',
'YOYOETH',
'YOYOUSDT',
'ZECBTC',
'ZECETH',
'ZECUSDT',
'ZENBTC',
'ZENETH',
'ZENUSDT',
'ZIPBTC',
'ZIPETH',
'ZIPUSDT',
'ZRXBTC',
'ZRXETH',
'ZRXUSDT'
	];
	$count = count($pairs);

	for ($i = 0; $i < $count; $i++) {
		$rules .= "\"".$pairs[$i]."\":{";
		$symbol = $symbols[$i];
		$rules .= "\"symbol\":\"$symbol\",";
		$rules .= "\"minPrice\":0,";
		$rules .= "\"maxPrice\":0,";
		$rules .= "\"minQty\":0,";
		$rules .= "\"maxQty\":0,";
		if (strpos($pairs[$i], "_usdt")) {
			$rules .= "\"aroundPrice\":4,";
		} else {
			$rules .= "\"aroundPrice\":8,";
		}
		$rules .= "\"aroundQty\":3,";
		$rules .= "\"minSum\":0,";
		$rules .= "\"maxSum\":0";
		$rules .= "},";
	}

	$rules = substr($rules, 0, -1) . "}}";

	echo $rules;
	exit;
}

//---------------------------- QUERY

function binance_query($path, $method, array $req = array()) {

	if(isset($_POST['secret'])) { $secret = $_POST['secret']; } elseif(isset($_GET['secret'])) { $secret = $_GET['secret']; } else { $secret = 0; }
	$secret = htmlspecialchars(strip_tags(trim($secret)));

	$post_data = http_build_query($req, '', '&');

	$post_data .= "&secret_key=$secret";
    $sign = strtoupper(md5($post_data));
   	$req['sign'] = $sign;

	$post_data = http_build_query($req, '', '&');
	$ch = null;
	if (is_null($ch)) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; okex PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
	}

	/*if ($method == 'GET') {
		$headers = array();
		$url = 'https://www.okex.com'.$path."?".$post_data;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
	}*/
	if ($method == 'POST') {
		$headers = array(
			'Content-Type: application/x-www-form-urlencoded',
		);
		$url = 'https://www.okex.com'.$path;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$res = curl_exec($ch);

	if ($res === false) {
		print "{\"success\":0}";
		exit;
	}

	$dec = json_decode($res, true);
	if (!$dec) {
		print "{\"success\":0}";
		exit;
	}
	return $dec;

}

//---------------------------- get Info

if ($method == 'getBalances'){

	$result = binance_query("/api/v1/userinfo.do","POST", array("api_key" => "$key",));

	if ($result['result'] == true) {
		$balances = "{";
		$balances .= "\"success\":1,";
		$balances .= "\"funds\":{";
		$count = count($result['balances']);

		foreach ($result['info']['funds']['free'] as $key => $value) {
			$balances .= "\"".strtolower($key)."\":".$value.",";
		}
		$balances = substr($balances, 0, -1) . "}}";
	} else {
		$balances = "{\"success\":0}";
	}

	echo $balances;
	exit;

}

//---------------------------- get History

if ($method == 'getTrades'){

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['since'])) { $since = $_POST['since']; } elseif(isset($_GET['since'])) { $since = $_GET['since']; } else { $since = 0; }
	$since = htmlspecialchars(strip_tags(trim($since)));

	$result = binance_query("/api/v1/order_history.do","POST", array("api_key" => "$key", "current_page" => "0", "page_length" => "200", "status" => "1", "symbol" => "$pair",));
	if ($result['result'] != true) {
		print "{\"success\":0}";
		exit;
	}
	$count = count($result['orders']);

		if ($count) {
			$trades = "{";
			$trades .= "\"success\":1,";
			$trades .= "\"trades\":[";
				for ($i = 0; $i < $count; $i++) {
				$k=0;
					if ($result['orders'][($count-$i-1)]['status'] == 2) {
						$trades .= "{\"pair\":\"".$pair."\",";
						$trades .= "\"type\":\"".$result['orders'][($count-$i-1)]['type']."\",";

						$trades .= "\"qty\":".$result['orders'][($count-$i-1)]['amount'].",";
						$trades .= "\"price\":".$result['orders'][($count-$i-1)]['price'].",";
						$trades .= "\"time\":".round($result['orders'][($count-$i-1)]['create_date']/1000,0)."},";
						$k++;
					}
				}
			$trades = substr($trades, 0, -1) . "]}";
		} else {
			$trades = "{\"success\":0}";
		}

	if ($k == 0) {
		print "{\"success\":0}";
		exit;
	}

	echo $trades;
	exit;
}

//---------------------------- get Active Orders

if ($method == 'getOrders') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	$result = binance_query("/api/v1/order_history.do","POST", array("api_key" => "$key", "current_page" => "0", "page_length" => "200", "status" => "0", "symbol" => "$pair",));
	if ($result['result'] != true) {
		print "{\"success\":0}";
		exit;
	}
	$count = count($result['orders']);

		if ($count) {
			$orders = "{";
			$orders .= "\"success\":1,";
			$orders .= "\"orders\":[";
				for ($i = 0; $i < $count; $i++) {
					$orders .= "{\"id\":".$result['orders'][$i]['order_id'].",";
					$orders .= "\"pair\":\"".$pair."\",";
					$orders .= "\"type\":\"".strtolower($result['orders'][$i]['type'])."\",";
					$orders .= "\"qty\":".$result['orders'][$i]['amount'].",";
					$orders .= "\"fill\":0,";
					$orders .= "\"price\":".$result['orders'][$i]['price'].",";
					$orders .= "\"time\":".round($result['orders'][$i]['create_date']/1000,0)."},";
				}
			$orders = substr($orders, 0, -1) . "]}";
		} else {
			$orders = "{\"success\":0}";
		}
	echo $orders;
	exit;


}

//---------------------------- get Order Book

if ($method == 'getDepth') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));
	if(isset($_POST['depth'])) { $depth = $_POST['depth']; } elseif(isset($_GET['depth'])) { $depth = $_GET['depth']; } else { $depth = 0; }
	$depth = htmlspecialchars(strip_tags(trim($depth)));

	$v = explode('_',$pair);

	$link = "https://www.okex.com/api/v1/depth.do?symbol=$pair&size=$depth";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$asks = $fcontents['asks'];
	$bids = $fcontents['bids'];

	$depth = "{";
	$depth .= "\"success\":1,";
	$depth .= "\"asks\":[";

	$count = count($asks);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$asks[($count-$i-1)][0].",".$asks[($count-$i-1)][1]."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "],\"bids\":[";
	$count = count($bids);
	for ($i=0; $i < $count;$i++) {
		$depth .= "[".$bids[$i][0].",".$bids[$i][1]."],";
	}
	$depth = substr($depth, 0, -1);
	$depth .= "]}";

	echo $depth;
	exit;
}

//---------------------------- cancel Order

if ($method == 'cancelOrder') {

	if(isset($_POST['id'])) { $id = $_POST['id']; } elseif(isset($_GET['id'])) { $id = $_GET['id']; } else { $id = 0; }
	$id = htmlspecialchars(strip_tags(trim($id)));

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	$result = binance_query("/api/v1/cancel_order.do","POST", array("api_key" => "$key", "order_id" => "$id", "symbol" => "$pair", ));

	if ($result['result'] == true) {
		$return = "{\"success\":1}";
	} else {
		$return = "{\"success\":0}";
	}

	echo $return;
	exit;

}

//---------------------------- TRADE

if ($method == 'sendOrder') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	if(isset($_POST['type'])) { $type = $_POST['type']; } elseif(isset($_GET['type'])) { $type = $_GET['type']; } else { $type = 0; }
	$type = htmlspecialchars(strip_tags(trim($type)));

	if(isset($_POST['qty'])) { $qty = $_POST['qty']; } elseif(isset($_GET['qty'])) { $qty = $_GET['qty']; } else { $qty = 0; }
	$qty = htmlspecialchars(strip_tags(trim($qty)));

	if(isset($_POST['price'])) { $price = $_POST['price']; } elseif(isset($_GET['price'])) { $price = $_GET['price']; } else { $price = 0; }
	$price = htmlspecialchars(strip_tags(trim($price)));

	$result = binance_query("/api/v1/trade.do","POST", array("amount" => $qty, "api_key" => "$key", "price" => $price, "symbol" => "$pair", "type" => "$type",));

	if ($result['result'] == true) {
		$time = time();
		$order = "{";
		$order .= "\"success\":1,";
		$order .= "\"order\":{";
		$order .= "\"id\":".$result['order_id'].",";
		$order .= "\"pair\":\"$pair\",";
		$order .= "\"type\":\"$type\",";
		$order .= "\"qty\":$qty,";
		$order .= "\"price\":$price,";
		$order .= "\"time\":$time";
		$order .= "}}";
	} else {
		$order = "{\"success\":0}";
	}

	echo $order;
	exit;

}

//---------------------------- get Prices

if ($method == 'getStrategyPrices') {

	if(isset($_POST['key'])) { $key = $_POST['key']; } elseif(isset($_GET['key'])) { $key = $_GET['key']; } else { $key = 0; }
	$key = htmlspecialchars(strip_tags(trim($key)));

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	if(isset($_POST['strategy'])) { $strategy = $_POST['strategy']; } elseif(isset($_GET['strategy'])) { $strategy = $_GET['strategy']; } else { $strategy = 0; }
	$strategy = htmlspecialchars(strip_tags(trim($strategy)));

	$link = "http://www.funnymay.com/api/okex_sapi.php?key=$key&pair=$pair&strategy=$strategy";
	$fcontents = implode ('', file ($link));

	echo $fcontents;
	exit;
}

//---------------------------- get Chart

if ($method == 'getChart') {

	if(isset($_POST['pair'])) { $pair = $_POST['pair']; } elseif(isset($_GET['pair'])) { $pair = $_GET['pair']; } else { $pair = 0; }
	$pair = htmlspecialchars(strip_tags(trim($pair)));

	$interval = "30min";
	$limit = 48;

	$link = "https://www.okex.com/api/v1/kline.do?symbol=$pair&type=$interval&size=$limit";
	$fcontents = implode ('', file ($link));
	$fcontents = json_decode($fcontents, true);

	$i=0;
	$j=0;
	$volumechart="";

	$chart = "<html>
<head>
<title>Chart</title>
<style>
body, html {
    height:100%;
    margin:0;
    padding:0;
}
</style>
</head>
<bodycellspacing='0' cellpadding='0'>
<script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
    <script type=\"text/javascript\">
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);
	  google.charts.setOnLoadCallback(drawChartVolume);

  function drawChart() {
    var data = google.visualization.arrayToDataTable([";

	while ($fcontents[$i]) {
		$lasttime = getdate($fcontents[$i][0]/1000);
		$tcandle[$j] =  date("H:i", mktime($lasttime["hours"], $lasttime["minutes"], 0, $lasttime["mon"], $lasttime["mday"], $lasttime["year"]));
		$lowcandle[$j] = $fcontents[$i][3];
		$opencandle[$j] = $fcontents[$i][1];
		$closecandle[$j] = $fcontents[$i][4];
		$highcandle[$j] = $fcontents[$i][2];
		$volumecandle[$j] = $fcontents[$i][5];
		$chart .= "[\"".$tcandle[$j]."\",".$lowcandle[$j].",".$opencandle[$j].",".$closecandle[$j].",".$highcandle[$j]."],";
		$volumechart .= "[\"".$tcandle[$j]."\",".$volumecandle[$j]."],";
		$i++;
		$j++;
	}

	$chart = substr ($chart, 0, -1);
	$volumechart = substr ($volumechart, 0, -1);

	$chart .= "    ], true);

    var options = {
      chartArea:{
		    left: 50,
		    top: 10,
		    width: 500,
		    height: 200
		  },
      legend:'none',
      colors: ['#515151', '#515151'],
      backgroundColor: {fill: '#131722', stroke: '#333333' },
      candlestick: {
            fallingColor: { strokeWidth: 0, fill: '#eb4d5c' },
            risingColor: { strokeWidth: 0, fill: '#53b987' }
          },
      hAxis: {
	        textStyle: {color: '#666666', fontSize: 12},
	        slantedTextAngle: 90
	      },
	  vAxis: {
	        gridlines: {color: '#333333'},
	        textStyle: {color: '#666666', fontSize: 12}
	      },
	  series: {0: {type: 'candlesticks'}, 1: {type: 'bars', targetAxisIndex:1, color:'#ebebeb'}}

    };

    var chart = new google.visualization.CandlestickChart(document.getElementById('chart_div'));

    chart.draw(data, options);
  }

  function drawChartVolume() {
    var data = google.visualization.arrayToDataTable([
    $volumechart
        ], true);

    var options = {
      chartArea:{
		    left: 50,
		    top: 10,
		    width: 500,
		    height: 100
		  },
	  hAxis: {
	        textStyle: {color: '#666666', fontSize: 12},
	        slantedTextAngle: 90
	      },
	  vAxis: {
	        gridlines: {color: '#333333'},
	        textStyle: {color: '#666666', fontSize: 12}

	      },
      legend:'none',
      colors: ['#515151', '#515151'],
      backgroundColor: {fill: '#131722', stroke: '#333333' }

    };
      var chart = new google.visualization.ColumnChart(document.getElementById('chart_div_volume'));

      chart.draw(data, options);

  }
</script>
<div id=\"chart_div\" style=\"width: 600px; height: 250px;\"></div>
<div id=\"chart_div_volume\" style=\"width: 600px; height: 150px;\"></div>
</body></html>";

	echo $chart;
	exit;

}

print "{\"success\":0}";
exit;

?>
