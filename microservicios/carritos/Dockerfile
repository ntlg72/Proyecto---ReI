FROM node 

EXPOSE 3003 

WORKDIR /carritos 

COPY src/controllers /carritos/controllers 

COPY src/models /carritos/models 

COPY src/index.js /carritos 

RUN npm init --yes 

RUN npm install express morgan mysql mysql2 axios 

CMD ["node", "index.js"] 
