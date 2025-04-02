const VueLoaderPlugin = require("vue-loader/lib/plugin");
const path = require("path");

module.exports = {
	entry: {
		admin: "./src/admin.js",
		index: "./src/index.js",
		room: "./src/room.js",
	},
	output: {
		path: path.resolve("./js"),
	},
	module: {
		rules: [
			{
				test: /\.vue$/,
				loader: "vue-loader",
			},
			{
				test: /\.js$/,
				loader: "babel-loader",
				options: {
					presets: [["@babel/preset-env"]],
				},
			},
			{
				test: /\.css$/,
				use: [
					"vue-style-loader",
					{
						loader: "css-loader",
						options: {
							url: false,
							esModule: false,
						},
					},
				],
			},
			{
				// Add this rule to handle image files
				test: /\.(png|jpe?g|gif|svg)$/i,
				type: "asset/resource", // For Webpack 5
				// If using Webpack 4 or earlier, use:
				// use: 'file-loader',
			},
		],
	},
	plugins: [new VueLoaderPlugin()],
	resolve: {
		extensions: [".js", ".vue"],
	},
};
