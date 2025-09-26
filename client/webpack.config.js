export default async () => {
	return {
		entry: {
			'client': './index.js',
		},
		output: {
			filename: 'script.js',
		},
		module: {
			rules: [
				{
					test: /\.css$/i,
					use: ["style-loader", "css-loader"],
				},
			],
		},
		devtool: 'source-map',
		mode: 'production',
	};
};