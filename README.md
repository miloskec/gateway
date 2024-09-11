# Gateway Microservice

[Please check the docs first](https://github.com/miloskec/gateway/blob/dockerhub-example/documentation/MicroServices%20gateway%20documentation.pdf)

## Setup Instructions

### 1. Project Setup

[Download this script for the project setup](https://github.com/miloskec/setupscript).

After downloading and executing the setup script(s), follow the next steps to complete the installation process.

### 2. Open AppSec Installation

Once the setup script has completed, you need to install Open AppSec for security protection and threat mitigation. Follow the instructions provided in the official Open AppSec documentation:

- [Open AppSec Installation for Kubernetes with NGINX Ingress](https://docs.openappsec.io/getting-started/start-with-kubernetes/install-using-interactive-cli-tool-ingress-nginx)

In this branch, we have:
- Updated the `gateway-deployment-service` with proper configurations.
- Created and configured `gateway-ingress` to enable external routing for the application, in preparation for Open AppSec.

### 3. Post-Setup Instructions

After completing the Open AppSec installation:
- Ensure that the NGINX Ingress is set up correctly for external traffic routing.
- Verify that Open AppSec is applied on top of your services for enhanced security.

For more detailed instructions and troubleshooting tips, refer to the Open AppSec documentation link above.

