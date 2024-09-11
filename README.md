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

For a detailed explanation of how it works, you can refer to the "Hello World" example project that integrates Ingress and Open AppSec. You can find the project here: Open AppSec Example Project.

[This example](https://github.com/miloskec/ingress-test-app) **provides a practical demonstration, walking through the essential setup and showcasing the functionality of Ingress and Open AppSec.**

# Open AppSec Installation Guide

## Instructions for Installing Open AppSec with Kubernetes Ingress

1. Download and run the Open AppSec Kubernetes Installer:
   ```bash
   wget https://downloads.openappsec.io/open-appsec-k8s-install && chmod +x open-appsec-k8s-install
   ./open-appsec-k8s-install
   ```

2. The installer will generate HELM charts/manifests (YAMLs), and you will be able to review and/or deploy them.

### Ingress Setup (Step 1 of 3)

When prompted, you will be given options for working with an existing Ingress resource:

- **Options**:
  1. Duplicate an existing Ingress and add open-appsec to it (existing and new ingresses will run side-by-side)
  2. Add open-appsec to an existing Ingress resource

   Follow the prompts to duplicate or add Open AppSec to your selected Ingress.

### Policy Setup (Step 2 of 3)

You can use the default policy or change it:

- **Rules**:
   - Name             : default
   - Description      : Web Application and API Best Practice
   - Namespace        : All
   - Ingress Rule     : All
   - Mode             : detect-learn
   - Protection Level : default
   - Log Triggers     : [appsec-log-trigger]
   - Web Response     : 403-forbidden

You may modify the rule by choosing options such as setting the mode, protection level, log trigger, and web response.

### Example: Change the Mode to "prevent-learn"

1. When prompted to choose the option for the rule setup, select "Set Mode".
2. Choose the option "prevent-learn" to change the rule to active prevention mode.
   
Save the updated rule configuration.

### Saving the Setup and Applying (Step 3 of 3)

1. Choose to save the manifest (YAML) or Helm chart:
   - `kubectl apply -f open-appsec-policy.yaml`
   - `helm install <filename>`

2. Run the following commands to apply the configuration:
   ```bash
   helm install open-appsec-k8s-nginx-ingress-latest.tgz --name-template open-appsec -n appsec --create-namespace --set appsec.mode=standalone --set appsec.persistence.enabled=false --set controller.ingressClassResource.name="appsec-nginx" --set controller.ingressClassResource.controllerValue="k8s.io/appsec-nginx" --set controller.terminationGracePeriodSeconds=300 --set appsec.playground=false --set appsec.userEmail=""
   ```

3. Apply the Ingress and policy YAML:
   ```bash
   kubectl apply -f ingress.yaml
   kubectl apply -f open-appsec-policy.yaml
   ```

4. Open AppSec installation completed!


### Open AppSec with Minikube

When using Minikube, you will need to start the tunnel. Once the tunnel is active, give it a few moments for Open AppSec Ingress to recognize and assign an IP address. After that, you can test the setup using either the Minikube IP or `localhost` (as previously defined in your hosts file). If you're working under WSL2, remember to update the hosts file on Windows as well. 
 
Once everything is configured, you can use the provided Postman collection, for example, to log in as an admin and verify that the service is responding correctly. After confirming the service functionality, you can test security aspects like SQL injection, as demonstrated in the **Login/Injection/login admin SQL Injection** test. This should return a `403 Forbidden` response, indicating that the OWASP protection is working as expected. 
 
For troubleshooting and support, visit:
[Open AppSec Support](https://openappsec.io/support)

For release notes and known limitations, visit:
[Release Notes](https://docs.openappsec.io/release-notes)
