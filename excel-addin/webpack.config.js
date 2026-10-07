const path = require("path");
const fs = require("fs");

const HtmlWebpackPlugin = require("html-webpack-plugin");
const CopyPlugin = require("copy-webpack-plugin");

module.exports = {
    entry: "./src/taskpane.js",

    output: {
        path: path.resolve(__dirname, "dist"),
        filename: "taskpane.js",
        clean: true
    },

    devServer: {
        static: {
            directory: path.join(__dirname, "dist")
        },

        server: {
            type: "https",
            options: {
                key: fs.readFileSync(
                    path.join(__dirname, "localhost.key")
                ),
                cert: fs.readFileSync(
                    path.join(__dirname, "localhost.crt")
                )
            }
        },

        host: "localhost",
        port: 3000,

        headers: {
            "Access-Control-Allow-Origin": "*"
        },

        hot: true
    },

    module: {
        rules: [
            {
                test: /\.css$/i,
                use: [
                    "style-loader",
                    "css-loader"
                ]
            }
        ]
    },

    plugins: [
        new HtmlWebpackPlugin({
            filename: "taskpane.html",
            template: "./src/taskpane.html"
        }),

        new CopyPlugin({
            patterns: [
                {
                    from: "assets",
                    to: "assets"
                },

                {
                    from: "manifest.xml",
                    to: "manifest.xml"
                }
            ]
        })
    ],

    resolve: {
        extensions: [".js"]
    },

    devtool: "source-map"
};