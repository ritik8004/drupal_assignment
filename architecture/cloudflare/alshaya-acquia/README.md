# Cloudflare Worker for Pausing ACM Queues

We publish the route `https://www.mothercare.com.kw/panic-on` from here to allow using it as Webhook in New Relic to pause ACM queues for specific sites whenever the sites are slow.

It is divided in three parts:
* Cloudflare Worker JavaScript Code (this folder)
* Secret information in Cloudflare KV NameSpace AlshayaAcquiaStability
  * To access or update the data utility scripts are available [here](../../../tests/apis/cloudflare)
* New Relic Webhook

[Check this recording to understand more about the same](https://acquiamagentoal.slack.com/files/UDEB422D7/F04EC8YGCET/alshaya_-_devops.mp4)

## 🔋 Getting Started

This template is meant to be used with [`wrangler`](https://github.com/cloudflare/wrangler). If you are not already familiar with the tool, we recommend that you install the tool and configure it to work with your [Cloudflare account](https://dash.cloudflare.com).

To generate using `wrangler`, run this command:

```bash
wrangler generate my-ts-project https://github.com/EverlastingBugstopper/worker-typescript-template
```

### 👩 💻 Developing

[`src/index.js`](https://github.com/EverlastingBugstopper/worker-typescript-template/blob/master/src/index.ts) calls the request handler in [`src/handler.ts`](https://github.com/EverlastingBugstopper/worker-typescript-template/blob/master/src/handler.ts), and will return the [request method](https://developer.mozilla.org/en-US/docs/Web/API/Request/method) for the given request.

### 🧪 Testing

This template comes with mocha tests which simply test that the request handler can handle each request method. `npm test` will run your tests.

### ✏️ Formatting

This template uses [`prettier`](https://prettier.io/) to format the project. To invoke, run `npm run format`.

### 👀 Previewing and Publishing

For information on how to preview and publish your worker, please see the `wrangler` [README](https://github.com/cloudflare/wrangler#%EF%B8%8F--publish).

## 🤢 Issues

If you run into issues with this specific project, please feel free to file an issue [here](https://github.com/EverlastingBugstopper/worker-typescript-template/issues). If the problem is with `wrangler`, please file an issue [here](https://github.com/cloudflare/wrangler/issues).

## ⚠️ Caveats

The `service-worker-mock` used by the tests is not a perfect representation of the Cloudflare Workers runtime. It is a general approximation. We recommend that you test with `wrangler preview` and in a staging environment to test things before deploying.
