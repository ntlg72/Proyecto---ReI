FROM node 

EXPOSE 3002 

WORKDIR /productos 

COPY src/controllers /productos/controllers 

COPY src/models /productos/models 

COPY src/index.js /productos 

RUN npm init --yes 

RUN npm install express morgan mysql mysql2 axios 

CMD ["node", "index.js"] 
