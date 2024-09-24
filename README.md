# Gateway Microservice [Please check the docs first](https://github.com/miloskec/gateway/blob/dockerhub-example/documentation/MicroServices%20gateway%20documentation.pdf)

## Setup Instructions

[You should download this script for the project setup](https://github.com/miloskec/setupscript)

## Kubernetes - multiple nodes 

If you are checking kubernetes-node-branch you must have created nodes in order to use them in deployment. 

```sh
minikube node add 
kubectl label nodes minikube app=gateway-node 
kubectl taint nodes minikube app=gateway:NoSchedule 

minikube node add 
kubectl label nodes minikube-m02 app=authentication-node 
kubectl taint nodes minikube-m02 app=authentication:NoSchedule 
...

```

Also deployment yaml file should have sections under the ***template -> spec**:
```yaml
      nodeSelector: 
        app: gateway-node 
      tolerations: 
      - key: "app" 
        operator: "Equal" 
        value: "gateway" 
        effect: "NoSchedule" 

        ...
```




