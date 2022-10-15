/**
 * Welcome to your Workbox-powered service worker!
 *
 * You'll need to register this file in your web app and you should
 * disable HTTP caching for this file too.
 * See https://goo.gl/nhQhGp
 *
 * The rest of the code is auto-generated. Please don't update this file
 * directly; instead, make changes to your Workbox build configuration
 * and re-run your build process.
 * See https://goo.gl/2aRDsh
 */

importScripts("https://storage.googleapis.com/workbox-cdn/releases/4.3.1/workbox-sw.js");

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

/**
 * The workboxSW.precacheAndRoute() method efficiently caches and responds to
 * requests for URLs in the manifest.
 * See https://goo.gl/S9QRab
 */
self.__precacheManifest = [
  {
    "url": "404.html",
    "revision": "f0270228df0ab4ee255e6fc34501ca2f"
  },
  {
    "url": "assets/css/0.styles.23cea4b6.css",
    "revision": "c2eddf336312d80c1cca73ec6993bf4b"
  },
  {
    "url": "assets/img/copied.26408bed.svg",
    "revision": "26408bed185146a74d6fb7d71b4207e9"
  },
  {
    "url": "assets/img/copy.e3634ccf.svg",
    "revision": "e3634ccf2a60445e59d5f255481010fd"
  },
  {
    "url": "assets/js/10.6748075e.js",
    "revision": "f632bdd18e47887c5e6036414458a99f"
  },
  {
    "url": "assets/js/11.6dc5edcd.js",
    "revision": "732a30a6c0fc838f1f2cb3c65e27145f"
  },
  {
    "url": "assets/js/12.a537d574.js",
    "revision": "0793400adce8a68d24fbfdbea75bb8af"
  },
  {
    "url": "assets/js/13.5a04bc66.js",
    "revision": "7a17c25b2a6d778fb8ebbbad0053214e"
  },
  {
    "url": "assets/js/14.eb8b2df2.js",
    "revision": "9e96e5ca644d0fb357450e61cc0a7524"
  },
  {
    "url": "assets/js/15.772c8057.js",
    "revision": "ff00da0a298d9f58d533dedb5289700d"
  },
  {
    "url": "assets/js/16.0e4c8c88.js",
    "revision": "b4259e132a41c860d812cf9ba8ffac98"
  },
  {
    "url": "assets/js/17.020cc786.js",
    "revision": "03cf432b7e21c11dba674465a07fd554"
  },
  {
    "url": "assets/js/18.d21cdb98.js",
    "revision": "5e9bf1de346e2c4ad67ebeb6a5da9810"
  },
  {
    "url": "assets/js/19.1fba23ce.js",
    "revision": "70811cd4ac48e77401cb61569ca872f7"
  },
  {
    "url": "assets/js/2.29fd2bee.js",
    "revision": "2030e5adf899d3ede923d78a7ef8f902"
  },
  {
    "url": "assets/js/20.8bd8d504.js",
    "revision": "c3b6b885e4555cddd698842197546a43"
  },
  {
    "url": "assets/js/21.a686e05f.js",
    "revision": "e9478f6b2357090cc3698a111d5838da"
  },
  {
    "url": "assets/js/22.354a492b.js",
    "revision": "22f345da050718c84577dae88fbbb262"
  },
  {
    "url": "assets/js/23.97547318.js",
    "revision": "cd2cada81bef7bd9aa68ab5a81f67c6d"
  },
  {
    "url": "assets/js/24.70f78891.js",
    "revision": "4d856f746f76d57ddc42a38c14cb944c"
  },
  {
    "url": "assets/js/25.89833498.js",
    "revision": "003e0ecd647f966449e4021dd47b5385"
  },
  {
    "url": "assets/js/26.355f5e50.js",
    "revision": "955ca23c28983eaf7426ca97bff27078"
  },
  {
    "url": "assets/js/27.576d5a51.js",
    "revision": "b2fb43fef6960fd2743924808d5c3377"
  },
  {
    "url": "assets/js/28.f606a4c6.js",
    "revision": "d0cd15d051538cd3fdab778dc3b0aa89"
  },
  {
    "url": "assets/js/29.a9ab16f2.js",
    "revision": "05529671adb671d60911c55bc5b34303"
  },
  {
    "url": "assets/js/3.3afb2573.js",
    "revision": "3a9066e9ba6a5cadc7279e6ff67b8b0e"
  },
  {
    "url": "assets/js/30.96ff5d60.js",
    "revision": "f8aebe92bd74c180841b4d7c23427ecc"
  },
  {
    "url": "assets/js/31.3b46671c.js",
    "revision": "ae6902bc61996305f36167c2a35d57bd"
  },
  {
    "url": "assets/js/32.7ed73df9.js",
    "revision": "9fbd1eb93576e6016cf6067e889440c0"
  },
  {
    "url": "assets/js/33.b12fb66c.js",
    "revision": "f6e80629b72f39ef79645e55917f38b9"
  },
  {
    "url": "assets/js/34.70c352b2.js",
    "revision": "8aeafa799eb37baa52dae08570a9d672"
  },
  {
    "url": "assets/js/35.7849e888.js",
    "revision": "4640c408070846e913a6fcb2bfe19dbe"
  },
  {
    "url": "assets/js/36.17c4181f.js",
    "revision": "f5cb31efa373e950f0f2daea05b97d46"
  },
  {
    "url": "assets/js/37.6c40a5ea.js",
    "revision": "ee36902c4bb457f46fe49c04ee6cf52d"
  },
  {
    "url": "assets/js/38.c1af9d2a.js",
    "revision": "1c8ecc0226758c7b08ac95d2ddc62f6e"
  },
  {
    "url": "assets/js/39.38ccde20.js",
    "revision": "5b13e45d454e3a3fc14f417724643ba4"
  },
  {
    "url": "assets/js/4.fcdb6b1d.js",
    "revision": "b12a36b8e74fa7fa535e30fb04628d4b"
  },
  {
    "url": "assets/js/40.a58d5508.js",
    "revision": "5070dbf474bd2e5f6023bb668c86cdc3"
  },
  {
    "url": "assets/js/41.59a02268.js",
    "revision": "c9582455c15bf95ee0d91cfe3914ffa0"
  },
  {
    "url": "assets/js/42.408b090a.js",
    "revision": "2bb090b809656cdc4c00c544c52a1f07"
  },
  {
    "url": "assets/js/43.c583490b.js",
    "revision": "376808cf2587c92f26b7673ef6e6521f"
  },
  {
    "url": "assets/js/44.6a75ee0c.js",
    "revision": "511b2cd0248d43a23c5cb5292a63db78"
  },
  {
    "url": "assets/js/45.96da8c6a.js",
    "revision": "b17d63fdd95907023cf76018c9351526"
  },
  {
    "url": "assets/js/46.4d1ddd93.js",
    "revision": "f0e4f241b3b92fc7ea03ef836cb3d5b0"
  },
  {
    "url": "assets/js/47.c7a37ea3.js",
    "revision": "033f001b7beb5670a70dab7f0b87349d"
  },
  {
    "url": "assets/js/48.8d0bd5be.js",
    "revision": "a64934cf43c42579814a6b802bfd9b40"
  },
  {
    "url": "assets/js/49.3f4fe975.js",
    "revision": "f00270158a3583d9e3de2c4afa707b73"
  },
  {
    "url": "assets/js/5.e3ff5e06.js",
    "revision": "cf5ba31367dedb5b491cee2444639d1d"
  },
  {
    "url": "assets/js/50.122ddb65.js",
    "revision": "6d874065f75477634e52daa06668e5dd"
  },
  {
    "url": "assets/js/51.ac17dc4e.js",
    "revision": "8042ac1066437d53884e436830e709bf"
  },
  {
    "url": "assets/js/52.7ed618b9.js",
    "revision": "275be3844949e9355281e1759a7aca0d"
  },
  {
    "url": "assets/js/53.69e1aa50.js",
    "revision": "c2bcded6c29b30486299e9a4178f9087"
  },
  {
    "url": "assets/js/54.b38e0290.js",
    "revision": "72cd45282ff4af3db495da4ff18d8966"
  },
  {
    "url": "assets/js/55.ea16c279.js",
    "revision": "6f810cca28ee14a4e8f143ca5a6505d8"
  },
  {
    "url": "assets/js/56.3d5b3fe6.js",
    "revision": "c9c899c01dc89eddf50f22d3457ecb08"
  },
  {
    "url": "assets/js/57.06a6622a.js",
    "revision": "90d48db32b5f30648b9b4ab74f2268da"
  },
  {
    "url": "assets/js/58.e2ae4944.js",
    "revision": "ebe7738eb7274e1909a3937fc1a1d7cd"
  },
  {
    "url": "assets/js/59.ee3285b8.js",
    "revision": "def24259bc34383af4a886525d6eb153"
  },
  {
    "url": "assets/js/6.0c603f08.js",
    "revision": "503271946bcebd429770647fae496c7e"
  },
  {
    "url": "assets/js/60.c3df131d.js",
    "revision": "37dee9484f2399586e7dbe287a80bd28"
  },
  {
    "url": "assets/js/61.3242a661.js",
    "revision": "360604bc592ea2af65fb26539f60110c"
  },
  {
    "url": "assets/js/62.6028414a.js",
    "revision": "2ac77ea51af58356ce3700e4d891ab08"
  },
  {
    "url": "assets/js/63.fe497bbb.js",
    "revision": "02644000dbc12cf99c69151c7613a8b6"
  },
  {
    "url": "assets/js/64.15712ce5.js",
    "revision": "0cb079eab6a6eec885634882bbaa75f4"
  },
  {
    "url": "assets/js/7.22d22add.js",
    "revision": "28ea59326bb10fb9c7fcecacc4d7f807"
  },
  {
    "url": "assets/js/8.da03dea6.js",
    "revision": "16f2f316f83a59eea96f8c26f65e2110"
  },
  {
    "url": "assets/js/9.b7d984b2.js",
    "revision": "a4fb0cff2ad598bd466adab2a37da648"
  },
  {
    "url": "assets/js/app.c2edf09d.js",
    "revision": "b14945dc5b18f4925567bf2fdbc3d542"
  },
  {
    "url": "concepts.html",
    "revision": "be839275493a510662700f8338d586ad"
  },
  {
    "url": "configuration.html",
    "revision": "7123467133b5e25855d85df69affc280"
  },
  {
    "url": "data.html",
    "revision": "6ca1ca2587330b5dd6bb821997227cfe"
  },
  {
    "url": "guide/integrations/attributes.html",
    "revision": "49286a288526a3db9272ea829ae4a43e"
  },
  {
    "url": "guide/integrations/browse.html",
    "revision": "d94642793c02034eb31dbcca9e2d8b9e"
  },
  {
    "url": "guide/integrations/commands.html",
    "revision": "cdc9a5ad8d718e8ee1137a26cb3e38cd"
  },
  {
    "url": "guide/integrations/create-folders.html",
    "revision": "9801e77c0673ed35aecfcd59514e06a9"
  },
  {
    "url": "guide/integrations/file-sharing.html",
    "revision": "d482663dc11392d6c7c0bcffb969953c"
  },
  {
    "url": "guide/integrations/magic-images.html",
    "revision": "f163f0c7a777503f43698350668e2cbe"
  },
  {
    "url": "guide/integrations/move-files.html",
    "revision": "282a2ef17b5cd199e47988c3919ad53c"
  },
  {
    "url": "guide/routes/dynamic-imaging.html",
    "revision": "d1773d6a6128e7ceef2b77d675a87ca0"
  },
  {
    "url": "guide/routes/endpoints.html",
    "revision": "afd10615b78b75d0a689e9bbb004a653"
  },
  {
    "url": "guide/routes/generating-urls.html",
    "revision": "d9560c674af7d78cc1cb7ef30fec2278"
  },
  {
    "url": "guide/routes/index.html",
    "revision": "6bc2df026be8ffc1927df39c62d6745e"
  },
  {
    "url": "guide/routes/public-endpoints.html",
    "revision": "e267c12e2ea51e7e98bd0f0b71f93f4e"
  },
  {
    "url": "guide/uploads/attachments.html",
    "revision": "fc8374dce06b32dba9891d00f5f1da20"
  },
  {
    "url": "guide/uploads/index.html",
    "revision": "a89ac00033557f7007b600fda7cee389"
  },
  {
    "url": "guide/uploads/media-storage.html",
    "revision": "6b68f6e2d66fc822c415730e59520dcd"
  },
  {
    "url": "icons/apple-touch-icon-152x152.png",
    "revision": "bb5d8a25d314cab9fb7003293e262b7b"
  },
  {
    "url": "icons/msapplication-icon-144x144.png",
    "revision": "7b147426540b00bc662c63140819dac9"
  },
  {
    "url": "index.html",
    "revision": "ef25bf89e98321e3d941129548feabda"
  },
  {
    "url": "installation/index.html",
    "revision": "359f3b910586c6b140b2cd00a9528c0e"
  },
  {
    "url": "installation/prepare-models.html",
    "revision": "fb1d7b76b11f231e2822aabe072ed807"
  },
  {
    "url": "logo.png",
    "revision": "a68c56ae1a0bc32fdcbf4d244b183aef"
  }
].concat(self.__precacheManifest || []);
workbox.precaching.precacheAndRoute(self.__precacheManifest, {});
addEventListener('message', event => {
  const replyPort = event.ports[0]
  const message = event.data
  if (replyPort && message && message.type === 'skip-waiting') {
    event.waitUntil(
      self.skipWaiting().then(
        () => replyPort.postMessage({ error: null }),
        error => replyPort.postMessage({ error })
      )
    )
  }
})
