module.exports = {
  title: "Media Library",
  description:
    "Manage media library with a directory system and associate files with Eloquent models.",
  base: "/media-library/",
  theme: "vt",
  themeConfig: {
    enableDarkMode: true,
    logo: "/logo.png",
    repo: "moirei/media-library",
    repoLabel: "Github",
    docsRepo: "moirei/media-library",
    docsDir: "docs",
    docsBranch: "master",
    sidebar: [
      {
        title: "Get started",
        sidebarDepth: 1, // optional, defaults to 1
        children: [
          "/installation/",
          "/installation/prepare-models",
          "/configuration",
          "/concepts",
        ],
      },
      {
        title: "Uploads",
        children: [
          "/guide/uploads/",
          "/guide/uploads/attachments",
          "/guide/uploads/media-storage",
        ],
      },
      {
        title: "Usage",
        children: [
          ["/guide/integrations/attributes", "Attributes & Casts"],
          ["/guide/integrations/create-folders", "Creating Folders"],
          ["/guide/integrations/move-files", "Moving Files & Folders"],
          ["/guide/integrations/browse", "Browsing Files"],
          ["/guide/integrations/magic-images", "Magic Images"],
          "/guide/integrations/file-sharing",
          ["/guide/integrations/commands", "Artisan Commands"],
        ],
      },
      {
        title: "API Routes",
        path: "/guide/routes/",
        children: [
          "/guide/routes/generating-urls",
          "/guide/routes/endpoints",
          "/guide/routes/public-endpoints",
          "/guide/routes/dynamic-imaging",
        ],
      },
    ],
    nav: [
      { text: "Guide", link: "/installation/prepare-models" },
      { text: "Concepts", link: "/concepts" },
      { text: "Dynamic Imaging", link: "/guide/routes/dynamic-imaging" },
      // { text: 'External', link: 'https://moirei.com', target:'_self', rel:false },
    ],
  },
  head: [
    ["link", { rel: "icon", href: "/logo.png" }],
    // ['link', { rel: 'manifest', href: '/manifest.json' }],
    ["meta", { name: "theme-color", content: "#3eaf7c" }],
    ["meta", { name: "apple-mobile-web-app-capable", content: "yes" }],
    [
      "meta",
      { name: "apple-mobile-web-app-status-bar-style", content: "black" },
    ],
    [
      "link",
      { rel: "apple-touch-icon", href: "/icons/apple-touch-icon-152x152.png" },
    ],
    // ['link', { rel: 'mask-icon', href: '/icons/safari-pinned-tab.svg', color: '#3eaf7c' }],
    [
      "meta",
      {
        name: "msapplication-TileImage",
        content: "/icons/msapplication-icon-144x144.png",
      },
    ],
    ["meta", { name: "msapplication-TileColor", content: "#000000" }],
  ],
  plugins: [
    "@vuepress/register-components",
    "@vuepress/active-header-links",
    "@vuepress/pwa",
    "seo",
  ],
};
