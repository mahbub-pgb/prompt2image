const path = require('path');

module.exports = {
  mode: 'production', // change to 'development' for dev builds with source maps
  entry: './spa/build/main.jsx', // entry point of your React SPA
  output: {
    filename: 'main.js', // compiled JS file
    path: path.resolve(__dirname, 'build'), // output folder
    publicPath: '/build/', // ensures assets are loaded correctly in WP
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/, // handles .js and .jsx files
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env', // compiles modern JS
              '@babel/preset-react' // compiles JSX
            ],
          },
        },
      },
      {
        test: /\.css$/i, // handles CSS imports
        use: [
          'style-loader', // injects CSS into the DOM
          'css-loader',   // resolves CSS imports
        ],
      },
      {
        test: /\.(png|jpe?g|gif|svg)$/i, // handles images
        type: 'asset/resource',
        generator: {
          filename: 'images/[name][ext]', // output images to build/images/
        },
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx'], // so you can import without extensions
    alias: {
      '@components': path.resolve(__dirname, 'spa/public/components/'), // optional shortcut
    },
  },
  devtool: 'source-map', // generates source maps for debugging
};
