const path = require("path");

module.exports = {
  plugins: [
    require("autoprefixer"),
    require("postcss-assets")({
      loadPaths: [path.resolve(__dirname, "public/img")],
    }),
  ],
};
