FROM node 

EXPOSE 3001 

WORKDIR /usuarios 

COPY src/controllers /usuarios/controllers 

COPY src/models /usuarios/models 

COPY src/index.js /usuarios 

RUN npm init --yes 

RUN npm install express morgan mysql mysql2 axios 

CMD ["node", "index.js"] 
